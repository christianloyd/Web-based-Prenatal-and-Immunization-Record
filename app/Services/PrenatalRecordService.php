<?php

namespace App\Services;

use App\Models\PrenatalCheckup;
use App\Models\PrenatalRecord;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\HealthcareNotification;
use Illuminate\Support\Facades\Cache;

class PrenatalRecordService
{
    /**
     * Create a new prenatal record
     */
    public function createPrenatalRecord(array $data)
    {
        try {
            // Check if patient already has an active prenatal record
            if ($this->hasActivePrenatalRecord($data['patient_id'])) {
                throw new \Exception('This patient already has an active prenatal record.');
            }

            $lmp = Carbon::parse($data['last_menstrual_period']);

            // Calculate gestational age
            $gestationalAge = $this->calculateGestationalAge($lmp);

            // Calculate trimester
            $totalDays = $lmp->diffInDays(now());
            $weeks = intval($totalDays / 7);
            $trimester = $weeks <= 12 ? 1 : ($weeks <= 26 ? 2 : 3);

            // Create prenatal record
            $prenatalRecord = PrenatalRecord::create([
                'patient_id' => $data['patient_id'],
                'last_menstrual_period' => $data['last_menstrual_period'],
                'expected_due_date' => $data['expected_due_date'] ?? $lmp->copy()->addDays(280)->toDateString(),
                'gestational_age' => $gestationalAge,
                'trimester' => $trimester,
                'gravida' => $data['gravida'] ?? null,
                'para' => $data['para'] ?? null,
                'medical_history' => $data['medical_history'] ?? null,
                'notes' => $data['notes'] ?? null,
                'blood_pressure' => $data['blood_pressure'] ?? null,
                'weight' => $data['weight'] ?? null,
                'height' => $data['height'] ?? null,
                'status' => 'normal',
            ]);

            // Send notification
            $this->notifyPrenatalRecordCreated($prenatalRecord, $gestationalAge);

            return $prenatalRecord;

        } catch (\Exception $e) {
            Log::error('Error creating prenatal record', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing prenatal record
     */
    public function updatePrenatalRecord(PrenatalRecord $prenatalRecord, array $data)
    {
        try {
            // If changing patient, check if the new patient already has an active record
            if ($data['patient_id'] != $prenatalRecord->patient_id) {
                if ($this->hasActivePrenatalRecord($data['patient_id'], $prenatalRecord->id)) {
                    throw new \Exception('The selected patient already has an active prenatal record.');
                }
            }

            $lmp = Carbon::parse($data['last_menstrual_period']);

            // Calculate gestational age
            $gestationalAge = $this->calculateGestationalAge($lmp);

            // Calculate trimester
            $totalDays = $lmp->diffInDays(now());
            $weeks = intval($totalDays / 7);
            $trimester = $weeks <= 12 ? 1 : ($weeks <= 26 ? 2 : 3);

            // Update prenatal record
            $prenatalRecord->update([
                'patient_id' => $data['patient_id'],
                'last_menstrual_period' => $data['last_menstrual_period'],
                'expected_due_date' => $data['expected_due_date'] ?? $lmp->copy()->addDays(280)->toDateString(),
                'gestational_age' => $gestationalAge,
                'trimester' => $trimester,
                'gravida' => $data['gravida'] ?? null,
                'para' => $data['para'] ?? null,
                'medical_history' => $data['medical_history'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'normal',
                'blood_pressure' => $data['blood_pressure'] ?? null,
                'weight' => $data['weight'] ?? null,
                'height' => $data['height'] ?? null,
            ]);

            return $prenatalRecord;

        } catch (\Exception $e) {
            Log::error('Error updating prenatal record', [
                'prenatal_record_id' => $prenatalRecord->id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Complete a pregnancy record (only by midwife)
     */
    public function completePregnancy(PrenatalRecord $prenatalRecord)
    {
        try {
            // Check if already completed
            if ($prenatalRecord->status === 'completed') {
                throw new \Exception('This pregnancy record is already completed.');
            }

            $cancelledCheckups = 0;

            DB::transaction(function () use ($prenatalRecord, &$cancelledCheckups) {
                // Refresh within transaction to avoid stale state
                $prenatalRecord->refresh();

                if ($prenatalRecord->status === 'completed') {
                    throw new \Exception('This pregnancy record is already completed.');
                }

                // Update status and active flag
                $prenatalRecord->update([
                    'status' => 'completed',
                    'is_active' => false,
                ]);

                $timestampNote = now()->format('M j, Y g:i A');

                $upcomingStatuses = ['upcoming', 'scheduled'];
                $upcomingCheckups = PrenatalCheckup::where('patient_id', $prenatalRecord->patient_id)
                    ->whereIn('status', $upcomingStatuses)
                    ->get();

                $cancelledCheckups = $upcomingCheckups->count();

                foreach ($upcomingCheckups as $checkup) {
                    $existingNotes = trim((string) $checkup->notes);
                    $noteSuffix = "[AUTO] Checkup cancelled because pregnancy record was completed on {$timestampNote}.";
                    $newNotes = $existingNotes ? $existingNotes . "\n\n" . $noteSuffix : $noteSuffix;

                    $checkup->update([
                        'status' => 'cancelled',
                        'notes' => $newNotes,
                    ]);
                }
            });

            $prenatalRecord->refresh()->loadMissing('patient');

            // Send notification with context
            $this->notifyPregnancyCompleted($prenatalRecord, $cancelledCheckups);

            return $prenatalRecord;

        } catch (\Exception $e) {
            Log::error('Error completing pregnancy record', [
                'prenatal_record_id' => $prenatalRecord->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate gestational age from LMP
     */
    private function calculateGestationalAge(Carbon $lmp)
    {
        $totalDays = $lmp->diffInDays(now());
        $weeks = intval($totalDays / 7);
        $days = $totalDays % 7;

        // Format gestational age
        if ($weeks == 0) {
            return $days == 1 ? "1 day" : "{$days} days";
        } elseif ($days == 0) {
            return $weeks == 1 ? "1 week" : "{$weeks} weeks";
        } else {
            $weekText = $weeks == 1 ? "1 week" : "{$weeks} weeks";
            $dayText = $days == 1 ? "1 day" : "{$days} days";
            return "{$weekText} {$dayText}";
        }
    }

    /**
     * Check if patient has an active prenatal record
     */
    private function hasActivePrenatalRecord($patientId, $excludeRecordId = null)
    {
        $query = PrenatalRecord::where('patient_id', $patientId)
            ->whereIn('status', ['normal', 'monitor', 'high-risk', 'due']);

        if ($excludeRecordId) {
            $query->where('id', '!=', $excludeRecordId);
        }

        return $query->exists();
    }

    /**
     * Send notification about new prenatal record
     */
    private function notifyPrenatalRecordCreated(PrenatalRecord $prenatalRecord, $gestationalAge)
    {
        $patient = Patient::find($prenatalRecord->patient_id);

        $this->notifyHealthcareWorkers(
            'New Prenatal Record Created',
            "A new prenatal record has been created for patient '{$patient->name}' (Gestational Age: {$gestationalAge}).",
            'info',
            Auth::user()->role === 'midwife'
                ? route('midwife.prenatalrecord.show', $prenatalRecord->id)
                : route('bhw.prenatalrecord.show', $prenatalRecord->id),
            [
                'prenatal_record_id' => $prenatalRecord->id,
                'patient_id' => $patient->id,
                'action' => 'prenatal_record_created'
            ]
        );
    }

    /**
     * Send notification about completed pregnancy
     */
    private function notifyPregnancyCompleted(PrenatalRecord $prenatalRecord, int $cancelledCheckups = 0)
    {
        try {
            $title = 'Pregnancy Completed';
            $message = 'Prenatal record for ' . $prenatalRecord->patient->name . ' has been marked as completed.';

            if ($cancelledCheckups > 0) {
                $plural = $cancelledCheckups === 1 ? 'checkup' : 'checkups';
                $message .= " {$cancelledCheckups} upcoming prenatal {$plural} were automatically cancelled.";
            }
            $type = 'success';
            $actionUrl = route('midwife.prenatalrecord.index');
            $data = [
                'notified_by' => Auth::user()->name,
                'notified_by_role' => Auth::user()->role
            ];

            // Notify admin
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new HealthcareNotification($title, $message, $type, $actionUrl, $data));
            }
        } catch (\Exception $e) {
            Log::error('Error sending completion notification: ' . $e->getMessage());
        }
    }

    /**
     * Notify all healthcare workers (midwives and BHWs)
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
