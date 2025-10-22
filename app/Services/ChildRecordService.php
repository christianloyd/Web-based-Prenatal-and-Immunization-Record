<?php

namespace App\Services;

use App\Models\ChildRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Notifications\HealthcareNotification;

class ChildRecordService
{
    /**
     * Create a new child record
     */
    public function createChildRecord(array $data)
    {
        try {
            $motherId = null;

            // Handle mother creation/selection
            if ($data['mother_exists'] === 'no') {
                $mother = $this->createMotherRecord([
                    'name' => $data['mother_name'],
                    'age' => $data['mother_age'],
                    'contact' => $this->formatPhoneNumber($data['mother_contact']),
                    'address' => $data['mother_address']
                ]);
                $motherId = $mother->id;

                Log::info('New mother created for child record', [
                    'mother_id' => $mother->id,
                    'mother_name' => $mother->name,
                    'created_by' => Auth::id()
                ]);
            } else {
                $motherId = $data['mother_id'];

                Log::info('Using existing mother for child record', [
                    'mother_id' => $motherId,
                    'selected_by' => Auth::id()
                ]);
            }

            // Prepare child record data
            $childData = [
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'gender' => $data['gender'],
                'birthdate' => $data['birthdate'],
                'birth_height' => $data['birth_height'] ?? null,
                'birth_weight' => $data['birth_weight'] ?? null,
                'birthplace' => $data['birthplace'] ?? null,
                'father_name' => $data['father_name'] ?? null,
                'phone_number' => $this->formatPhoneNumber($data['phone_number']),
                'address' => $data['address'] ?? null,
                'mother_id' => $motherId
            ];

            // Create child record
            $childRecord = ChildRecord::create($childData);

            Log::info('Child record created successfully', [
                'child_record_id' => $childRecord->id,
                'child_name' => $childRecord->full_name,
                'mother_id' => $motherId,
                'mother_exists' => $data['mother_exists'],
                'created_by' => Auth::id()
            ]);

            // Send notification
            $mother = Patient::find($motherId);
            $this->notifyChildRecordCreated($childRecord, $mother);

            return $childRecord;

        } catch (\Exception $e) {
            Log::error('Error in createChildRecord', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing child record
     */
    public function updateChildRecord(ChildRecord $childRecord, array $data)
    {
        try {
            $childRecord->update($data);

            Log::info('Child record updated successfully', [
                'id' => $childRecord->id,
                'updated_by_role' => Auth::user()->role,
                'updated_data' => $data
            ]);

            return $childRecord;

        } catch (\Exception $e) {
            Log::error('Error updating child record', [
                'child_record_id' => $childRecord->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Create a mother (patient) record
     */
    private function createMotherRecord(array $data)
    {
        try {
            $data['formatted_patient_id'] = Patient::generatePatientId();

            return Patient::create($data);

        } catch (\Exception $e) {
            Log::error('Error creating mother record', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw new \Exception('Error creating mother record. Please try again.');
        }
    }

    /**
     * Format phone number to consistent format (+63 format)
     */
    public function formatPhoneNumber($phone)
    {
        if (empty($phone)) {
            return $phone;
        }

        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // Convert to +63 format
        if (substr($digits, 0, 2) === '63') {
            return '+' . $digits;
        } elseif (substr($digits, 0, 1) === '0') {
            return '+63' . substr($digits, 1);
        } elseif (strlen($digits) === 10) {
            return '+63' . $digits;
        }

        return $phone; // Return original if can't format
    }

    /**
     * Send notification about new child record
     */
    private function notifyChildRecordCreated(ChildRecord $childRecord, Patient $mother)
    {
        $this->notifyHealthcareWorkers(
            'New Child Record Created',
            "A new child record has been created for '{$childRecord->full_name}' (Mother: {$mother->name}).",
            'success',
            Auth::user()->role === 'midwife'
                ? route('midwife.childrecord.show', $childRecord->id)
                : route('bhw.childrecord.show', $childRecord->id),
            ['child_record_id' => $childRecord->id, 'mother_id' => $mother->id, 'action' => 'child_record_created']
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
