<?php

namespace App\Services;

use App\Models\Immunization;
use App\Models\ChildRecord;
use App\Models\Vaccine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Notifications\HealthcareNotification;

class ImmunizationService
{
    /**
     * Create a new immunization record
     */
    public function createImmunization(array $data)
    {
        DB::beginTransaction();

        try {
            // Check vaccine availability
            $vaccine = Vaccine::findOrFail($data['vaccine_id']);
            if ($vaccine->current_stock <= 0) {
                throw new \Exception("The vaccine '{$vaccine->name}' is currently out of stock.");
            }

            // Check if child already has an upcoming immunization
            if ($this->hasUpcomingImmunization($data['child_record_id'])) {
                throw new \Exception('This child already has an upcoming immunization scheduled. Please complete or reschedule the existing one first.');
            }

            // Prepare immunization data
            $immunizationData = $data;
            $immunizationData['status'] = 'Upcoming';
            $immunizationData['vaccine_name'] = $vaccine->name; // Store for backward compatibility

            // Calculate next due date
            $immunizationData['next_due_date'] = $this->calculateNextDueDate(
                $vaccine->name,
                $data['dose'],
                $data['schedule_date']
            );

            $immunization = Immunization::create($immunizationData);

            // Send notification
            $child = ChildRecord::findOrFail($data['child_record_id']);
            $this->notifyImmunizationScheduled($immunization, $child, $vaccine, $data['schedule_date']);

            // Send SMS reminder to parent/guardian
            $child = ChildRecord::with('mother')->findOrFail($data['child_record_id']);
            $mother = $child->mother;
            $contactNumber = $mother ? $mother->contact : null;

            if ($contactNumber) {
                try {
                    $smsService = new SmsService();
                    $formattedDate = Carbon::parse($data['schedule_date'])->format('F j, Y');
                    $formattedTime = isset($data['schedule_time']) ? Carbon::parse($data['schedule_time'])->format('g:i A') : '';
                    $smsService->sendVaccinationReminder(
                        $contactNumber,
                        $child->full_name,
                        $vaccine->name,
                        $formattedDate . ($formattedTime ? ' at ' . $formattedTime : ''),
                        $mother->name ?? null
                    );
                    Log::info('SMS vaccination reminder sent', [
                        'child_id' => $child->id,
                        'immunization_id' => $immunization->id,
                        'phone' => $contactNumber
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send SMS for immunization: ' . $e->getMessage());
                    // Don't fail the immunization creation if SMS fails
                }
            }

            DB::commit();

            return $immunization;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating immunization record', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing immunization record
     */
    public function updateImmunization(Immunization $immunization, array $data)
    {
        DB::beginTransaction();

        try {
            $oldStatus = $immunization->status;

            // If changing to Done status, check and consume stock
            if ($data['status'] === 'Done' && $oldStatus !== 'Done') {
                $vaccine = Vaccine::findOrFail($data['vaccine_id']);

                if ($vaccine->current_stock <= 0) {
                    throw new \Exception("Cannot mark as done - vaccine '{$vaccine->name}' is out of stock.");
                }

                // Consume vaccine stock
                $vaccine->updateStock(1, 'out', "Immunization administered to {$immunization->childRecord->full_name}");
            }

            // If changing vaccine, check availability
            if ($data['vaccine_id'] != $immunization->vaccine_id) {
                $newVaccine = Vaccine::findOrFail($data['vaccine_id']);
                if ($newVaccine->current_stock <= 0) {
                    throw new \Exception("The vaccine '{$newVaccine->name}' is currently out of stock.");
                }
                $data['vaccine_name'] = $newVaccine->name;
            }

            // Calculate next due date
            $vaccine = Vaccine::findOrFail($data['vaccine_id']);
            $data['next_due_date'] = $this->calculateNextDueDate(
                $vaccine->name,
                $data['dose'],
                $data['schedule_date']
            );

            $immunization->update($data);

            // Get child record for notifications
            $child = ChildRecord::findOrFail($data['child_record_id']);

            // Send notification if status changed to Done
            if ($oldStatus !== 'Done' && $data['status'] === 'Done') {
                $this->notifyImmunizationCompleted($immunization, $child, $vaccine);
            }

            // Send SMS if schedule date was changed and status is still Upcoming
            if ($data['status'] === 'Upcoming' &&
                isset($data['schedule_date']) &&
                $immunization->getOriginal('schedule_date') != $data['schedule_date']) {

                $child = ChildRecord::with('mother')->findOrFail($data['child_record_id']);
                $mother = $child->mother;
                $contactNumber = $mother ? $mother->contact : null;

                if ($contactNumber) {
                    try {
                        $smsService = new SmsService();
                        $formattedDate = Carbon::parse($data['schedule_date'])->format('F j, Y');
                        $formattedTime = isset($data['schedule_time']) ? Carbon::parse($data['schedule_time'])->format('g:i A') : '';
                        $smsService->sendVaccinationReminder(
                            $contactNumber,
                            $child->full_name,
                            $vaccine->name,
                            $formattedDate . ($formattedTime ? ' at ' . $formattedTime : ''),
                            $mother->name ?? null
                        );
                        Log::info('SMS vaccination reminder sent (rescheduled)', [
                            'child_id' => $child->id,
                            'immunization_id' => $immunization->id,
                            'phone' => $contactNumber
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send SMS for rescheduled immunization: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return $immunization;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating immunization record', [
                'immunization_id' => $immunization->id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Mark immunization as Done or Missed
     */
    public function markStatus(Immunization $immunization, string $status)
    {
        DB::beginTransaction();

        try {
            if (!in_array($status, ['Done', 'Missed'])) {
                throw new \Exception('Invalid status update.');
            }

            // If marking as Done, consume vaccine stock
            if ($status === 'Done' && $immunization->status !== 'Done') {
                if (!$immunization->vaccine) {
                    throw new \Exception('Cannot mark as done - vaccine information is missing.');
                }

                if ($immunization->vaccine->current_stock <= 0) {
                    throw new \Exception("Cannot mark as done - vaccine '{$immunization->vaccine->name}' is out of stock.");
                }

                // Consume vaccine stock
                $immunization->vaccine->updateStock(
                    1,
                    'out',
                    "Immunization administered to {$immunization->childRecord->full_name}"
                );
            }

            $immunization->status = $status;
            $immunization->save();

            DB::commit();

            return $immunization;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating immunization status', [
                'immunization_id' => $immunization->id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Quick update immunization status via AJAX
     */
    public function quickUpdateStatus(Immunization $immunization, array $data)
    {
        DB::beginTransaction();

        try {
            $child = $immunization->childRecord;
            $child->load('mother'); // Load mother relationship for SMS
            $vaccine = $immunization->vaccine;

            // If marking as Done, consume vaccine stock
            if ($data['status'] === 'Done' && $immunization->status !== 'Done') {
                if (!$vaccine) {
                    throw new \Exception('Cannot mark as done - vaccine information is missing.');
                }

                if ($vaccine->current_stock <= 0) {
                    throw new \Exception("Cannot mark as done - vaccine '{$vaccine->name}' is out of stock.");
                }

                // Consume vaccine stock
                $vaccine->updateStock(
                    1,
                    'out',
                    "Immunization administered to {$child->full_name}"
                );

                // Create child immunization record
                \App\Models\ChildImmunization::create([
                    'child_record_id' => $immunization->child_record_id,
                    'vaccine_name' => $vaccine->name ?? $immunization->vaccine_name,
                    'vaccine_description' => $vaccine->description ?? '',
                    'vaccination_date' => $immunization->schedule_date,
                    'administered_by' => $data['administered_by'] ?? Auth::user()->name,
                    'batch_number' => $data['batch_number'] ?? null,
                    'notes' => $data['notes'] ?? 'Marked done via quick action'
                ]);

                // Send SMS notification for completed immunization
                $mother = $child->mother;
                $contactNumber = $mother ? $mother->contact : null;

                if ($contactNumber) {
                    try {
                        $smsService = new SmsService();
                        $formattedDate = Carbon::parse($immunization->schedule_date)->format('M j, Y'); // Shorter date format
                        // Optimized message to stay under 160 characters to avoid double charging
                        $childName = $child->full_name;
                        $vaccineName = $vaccine->name;

                        // Build concise message
                        $message = "Hi {$mother}, your child {$childName}'s {$vaccineName} vaccination completed on {$formattedDate}. Thank you!";

                        // Only add sender name if it fits within 160 chars
                        $senderName = config('services.iprog.sender_name');
                        if (strlen($message . " - " . $senderName) <= 160) {
                            $message .= " - {$senderName}";
                        }

                        $smsService->sendSms($contactNumber, $message, 'immunization_completed', $mother->name ?? $child->full_name, 'Immunization', $immunization->id);
                        Log::info('SMS sent for completed immunization', [
                            'child_id' => $child->id,
                            'immunization_id' => $immunization->id,
                            'phone' => $contactNumber,
                            'message_length' => strlen($message)
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send SMS for completed immunization: ' . $e->getMessage());
                    }
                }
            }

            // If marking as Missed
            if ($data['status'] === 'Missed') {
                // Store the reason in notes if provided
                if (!empty($data['reason'])) {
                    $immunization->notes = ($immunization->notes ? $immunization->notes . ' | ' : '') . 'Missed Reason: ' . $data['reason'];
                    if (!empty($data['notes'])) {
                        $immunization->notes .= ' - ' . $data['notes'];
                    }
                }

                // Handle rescheduling if requested
                if (!empty($data['reschedule']) && $data['reschedule'] === true) {
                    if (empty($data['reschedule_date'])) {
                        throw new \Exception('Reschedule date is required when rescheduling.');
                    }

                    // Create new immunization record for the rescheduled appointment
                    $newScheduleDate = $data['reschedule_date'];
                    if (!empty($data['reschedule_time'])) {
                        $newScheduleDate .= ' ' . $data['reschedule_time'];
                    }

                    $rescheduledImmunization = Immunization::create([
                        'child_record_id' => $immunization->child_record_id,
                        'vaccine_id' => $immunization->vaccine_id,
                        'vaccine_name' => $immunization->vaccine_name,
                        'dose' => $immunization->dose,
                        'schedule_date' => $newScheduleDate,
                        'status' => 'Upcoming',
                        'notes' => 'Rescheduled from missed appointment on ' . Carbon::parse($immunization->schedule_date)->format('M d, Y')
                    ]);

                    // Send SMS for rescheduled immunization
                    $mother = $child->mother;
                    $contactNumber = $mother ? $mother->contact : null;

                    if ($contactNumber) {
                        try {
                            $smsService = new SmsService();
                            $formattedDate = Carbon::parse($newScheduleDate)->format('F j, Y');
                            $formattedTime = !empty($data['reschedule_time']) ? Carbon::parse($data['reschedule_time'])->format('g:i A') : '';
                            $smsService->sendVaccinationReminder(
                                $contactNumber,
                                $child->full_name,
                                $vaccine->name ?? $immunization->vaccine_name,
                                $formattedDate . ($formattedTime ? ' at ' . $formattedTime : ''),
                                $mother->name ?? null
                            );
                            Log::info('SMS sent for rescheduled immunization', [
                                'child_id' => $child->id,
                                'new_immunization_id' => $rescheduledImmunization->id,
                                'phone' => $contactNumber
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to send SMS for rescheduled immunization: ' . $e->getMessage());
                        }
                    }
                } else {
                    // Send SMS for missed immunization (without reschedule)
                    $mother = $child->mother;
                    $contactNumber = $mother ? $mother->contact : null;

                    if ($contactNumber) {
                        try {
                            $smsService = new SmsService();
                            $formattedDate = Carbon::parse($immunization->schedule_date)->format('F j, Y');
                            $smsService->sendMissedAppointmentNotification(
                                $contactNumber,
                                $child->full_name,
                                $formattedDate,
                                $mother->name ?? null
                            );
                            Log::info('SMS sent for missed immunization', [
                                'child_id' => $child->id,
                                'immunization_id' => $immunization->id,
                                'phone' => $contactNumber
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to send SMS for missed immunization: ' . $e->getMessage());
                        }
                    }
                }
            }

            // Update immunization status
            $immunization->status = $data['status'];
            $immunization->save();

            DB::commit();

            return $immunization;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error quick updating immunization status', [
                'immunization_id' => $immunization->id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Check if child has an upcoming immunization
     */
    private function hasUpcomingImmunization($childRecordId)
    {
        return Immunization::where('child_record_id', $childRecordId)
            ->where('status', 'Upcoming')
            ->exists();
    }

    /**
     * Calculate next due date based on vaccine type and dose
     */
    public function calculateNextDueDate($vaccineName, $dose, $currentDate)
    {
        $date = Carbon::parse($currentDate);

        $intervals = [
            'BCG' => null,
            'Hepatitis B' => [
                '1st Dose' => 30,
                '2nd Dose' => 150,
                '3rd Dose' => null
            ],
            'DPT' => [
                '1st Dose' => 30,
                '2nd Dose' => 30,
                '3rd Dose' => 365,
                'Booster' => null
            ],
            'OPV' => [
                '1st Dose' => 30,
                '2nd Dose' => 30,
                '3rd Dose' => null
            ],
            'MMR' => [
                '1st Dose' => 365,
                '2nd Dose' => null
            ]
        ];

        if (isset($intervals[$vaccineName])) {
            if (is_array($intervals[$vaccineName])) {
                $daysToAdd = $intervals[$vaccineName][$dose] ?? null;
            } else {
                $daysToAdd = $intervals[$vaccineName];
            }

            if ($daysToAdd) {
                return $date->addDays($daysToAdd)->toDateString();
            }
        }

        return null;
    }

    /**
     * Send notification about scheduled immunization
     */
    private function notifyImmunizationScheduled(Immunization $immunization, ChildRecord $child, Vaccine $vaccine, $scheduleDate)
    {
        $this->notifyHealthcareWorkers(
            'New Immunization Scheduled',
            "Immunization for {$vaccine->name} has been scheduled for {$child->full_name} on " . Carbon::parse($scheduleDate)->format('M d, Y'),
            'info',
            Auth::user()->role === 'midwife'
                ? route('midwife.immunization.index')
                : route('bhw.immunization.index'),
            ['immunization_id' => $immunization->id, 'child_id' => $child->id, 'action' => 'immunization_scheduled']
        );
    }

    /**
     * Send notification about completed immunization
     */
    private function notifyImmunizationCompleted(Immunization $immunization, ChildRecord $child, Vaccine $vaccine)
    {
        $this->notifyHealthcareWorkers(
            'Immunization Completed',
            "Immunization for {$vaccine->name} has been completed for {$child->full_name}",
            'success',
            Auth::user()->role === 'midwife'
                ? route('midwife.immunization.index')
                : route('bhw.immunization.index'),
            ['immunization_id' => $immunization->id, 'child_id' => $child->id, 'action' => 'immunization_completed']
        );
    }

    /**
     * Notify all healthcare workers
     */
    private function notifyHealthcareWorkers($title, $message, $type = 'info', $actionUrl = null, $data = [])
    {
        $healthcareWorkers = User::whereIn('role', ['midwife', 'bhw'])
            ->where('id', '!=', Auth::id())
            ->get();

        foreach ($healthcareWorkers as $worker) {
            $worker->notify(new HealthcareNotification(
                $title,
                $message,
                $type,
                $actionUrl,
                array_merge($data, ['notified_by' => Auth::user()->name])
            ));

            Cache::forget("unread_notifications_count_{$worker->id}");
            Cache::forget("recent_notifications_{$worker->id}");
        }
    }
}
