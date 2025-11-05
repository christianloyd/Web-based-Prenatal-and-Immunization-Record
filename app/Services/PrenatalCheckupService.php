<?php

namespace App\Services;

use App\Models\PrenatalCheckup;
use App\Models\Patient;
use App\Models\PrenatalRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrenatalCheckupService
{
    /**
     * Create a new prenatal checkup
     *
     * @param array $data Validated data
     * @return PrenatalCheckup
     * @throws \Exception
     */
    public function createCheckup(array $data)
    {
        DB::beginTransaction();

        try {
            // Get patient and their active prenatal record
            $patient = Patient::findOrFail($data['patient_id']);
            $prenatalRecord = $patient->prenatalRecords()->where('is_active', true)->first();

            // Determine status based on whether medical data is provided
            $hasMedicalData = isset($data['weight_kg']) || isset($data['blood_pressure_systolic']) ||
                             isset($data['fetal_heart_rate']) || isset($data['fundal_height_cm']) ||
                             isset($data['symptoms']) || isset($data['notes']);

            $status = $hasMedicalData ? 'done' : 'upcoming';

            // Check if there's an existing 'upcoming' checkup for this date
            $existingCheckup = PrenatalCheckup::where('patient_id', $data['patient_id'])
                ->whereDate('checkup_date', $data['checkup_date'])
                ->first();

            // If existing checkup is 'upcoming', update it instead of creating new
            if ($existingCheckup && $existingCheckup->status === 'upcoming') {
                $checkup = $this->updateExistingCheckup($existingCheckup, $data, $status);
            } else {
                $checkup = $this->createNewCheckup($data, $prenatalRecord, $status);
            }

            // If scheduling next visit, create another upcoming checkup
            if (isset($data['next_visit_date']) && isset($data['schedule_next']) && $data['schedule_next']) {
                $this->scheduleNextVisit($data, $patient, $prenatalRecord);
            }

            DB::commit();

            return $checkup;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update existing upcoming checkup
     */
    protected function updateExistingCheckup($checkup, array $data, string $status)
    {
        $checkup->update([
            'checkup_time' => $data['checkup_time'],
            'gestational_age_weeks' => $data['gestational_age_weeks'] ?? null,
            'weight_kg' => $data['weight_kg'] ?? null,
            'blood_pressure_systolic' => $data['blood_pressure_systolic'] ?? null,
            'blood_pressure_diastolic' => $data['blood_pressure_diastolic'] ?? null,
            'fetal_heart_rate' => $data['fetal_heart_rate'] ?? null,
            'fundal_height_cm' => $data['fundal_height_cm'] ?? null,
            'baby_movement' => $data['baby_movement'] ?? null,
            'symptoms' => $data['symptoms'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $status,
            'next_visit_date' => $data['next_visit_date'] ?? null,
            'next_visit_time' => $data['next_visit_time'] ?? null,
            'next_visit_notes' => $data['next_visit_notes'] ?? null,
            'conducted_by' => $data['conducted_by'] ?? Auth::id(),
            // Legacy fields for backward compatibility
            'bp_high' => $data['blood_pressure_systolic'] ?? null,
            'bp_low' => $data['blood_pressure_diastolic'] ?? null,
            'weight' => $data['weight_kg'] ?? null,
            'baby_heartbeat' => $data['fetal_heart_rate'] ?? null,
            'belly_size' => $data['fundal_height_cm'] ?? null,
        ]);

        return $checkup;
    }

    /**
     * Create new prenatal checkup
     */
    protected function createNewCheckup(array $data, $prenatalRecord, string $status)
    {
        return PrenatalCheckup::create([
            'patient_id' => $data['patient_id'],
            'prenatal_record_id' => $prenatalRecord ? $prenatalRecord->id : null,
            'checkup_date' => $data['checkup_date'],
            'checkup_time' => $data['checkup_time'],
            'gestational_age_weeks' => $data['gestational_age_weeks'] ?? null,
            'weight_kg' => $data['weight_kg'] ?? null,
            'blood_pressure_systolic' => $data['blood_pressure_systolic'] ?? null,
            'blood_pressure_diastolic' => $data['blood_pressure_diastolic'] ?? null,
            'fetal_heart_rate' => $data['fetal_heart_rate'] ?? null,
            'fundal_height_cm' => $data['fundal_height_cm'] ?? null,
            'baby_movement' => $data['baby_movement'] ?? null,
            'symptoms' => $data['symptoms'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $status,
            'next_visit_date' => $data['next_visit_date'] ?? null,
            'next_visit_time' => $data['next_visit_time'] ?? null,
            'next_visit_notes' => $data['next_visit_notes'] ?? null,
            'conducted_by' => $data['conducted_by'] ?? Auth::id(),
            // Legacy fields for backward compatibility
            'bp_high' => $data['blood_pressure_systolic'] ?? null,
            'bp_low' => $data['blood_pressure_diastolic'] ?? null,
            'weight' => $data['weight_kg'] ?? null,
            'baby_heartbeat' => $data['fetal_heart_rate'] ?? null,
            'belly_size' => $data['fundal_height_cm'] ?? null,
        ]);
    }

    /**
     * Schedule next visit
     */
    protected function scheduleNextVisit(array $data, $patient, $prenatalRecord)
    {
        // Check if a checkup already exists for the next visit date
        $existingNextCheckup = PrenatalCheckup::where('patient_id', $patient->id)
            ->whereDate('checkup_date', $data['next_visit_date'])
            ->first();

        if (!$existingNextCheckup) {
            $nextCheckup = PrenatalCheckup::create([
                'patient_id' => $patient->id,
                'prenatal_record_id' => $prenatalRecord ? $prenatalRecord->id : null,
                'checkup_date' => $data['next_visit_date'],
                'checkup_time' => $data['next_visit_time'] ?? '09:00',
                'gestational_age_weeks' => null,
                'status' => 'upcoming',
                'conducted_by' => Auth::id(),
            ]);

            // Send SMS reminder for next visit (Prenatal Checkup Reminder type)
            $this->sendCheckupReminder($patient, $nextCheckup);
        }
    }

    /**
     * Send SMS reminder for checkup
     */
    protected function sendCheckupReminder($patient, $checkup)
    {
        if (!$patient->contact) {
            return;
        }

        try {
            $smsService = new SmsService();
            $formattedDate = Carbon::parse($checkup->checkup_date)->format('F j, Y');
            $formattedTime = $checkup->checkup_time ? Carbon::parse($checkup->checkup_time)->format('g:i A') : '';

            $message = "Hello {$patient->name}! This is a reminder for your prenatal checkup scheduled on {$formattedDate}";
            if ($formattedTime) {
                $message .= " at {$formattedTime}";
            }
            $message .= ". Please don't forget to bring your prenatal record. - " . config('services.iprog.sender_name');

            $smsService->sendSms(
                $patient->contact,
                $message,
                'prenatal_checkup_reminder',
                $patient->name,
                'PrenatalCheckup',
                $checkup->id
            );

            \Log::info('Prenatal checkup SMS reminder sent', [
                'patient_id' => $patient->id,
                'checkup_id' => $checkup->id,
                'phone' => $patient->contact
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send prenatal checkup SMS reminder: ' . $e->getMessage());
            // Don't fail the checkup creation if SMS fails
        }
    }

    /**
     * Update an existing prenatal checkup
     *
     * @param PrenatalCheckup $checkup
     * @param array $data Validated data
     * @return PrenatalCheckup
     * @throws \Exception
     */
    public function updateCheckup(PrenatalCheckup $checkup, array $data)
    {
        DB::beginTransaction();

        try {
            $checkup->update([
                'prenatal_record_id' => $data['prenatal_record_id'],
                'checkup_date' => $data['checkup_date'],
                'gestational_age_weeks' => $data['gestational_age_weeks'] ?? null,
                'weight_kg' => $data['weight_kg'] ?? null,
                'blood_pressure_systolic' => $data['blood_pressure_systolic'] ?? null,
                'blood_pressure_diastolic' => $data['blood_pressure_diastolic'] ?? null,
                'fetal_heart_rate' => $data['fetal_heart_rate'] ?? null,
                'fundal_height_cm' => $data['fundal_height_cm'] ?? null,
                'presentation' => $data['presentation'] ?? null,
                'symptoms' => $data['symptoms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'next_visit_date' => $data['next_visit_date'] ?? null,
                'conducted_by' => $data['conducted_by'] ?? null,
                'status' => $data['status'],
                // Legacy fields
                'bp_high' => $data['blood_pressure_systolic'] ?? null,
                'bp_low' => $data['blood_pressure_diastolic'] ?? null,
                'weight' => $data['weight_kg'] ?? null,
                'baby_heartbeat' => $data['fetal_heart_rate'] ?? null,
                'belly_size' => $data['fundal_height_cm'] ?? null,
            ]);

            DB::commit();

            return $checkup;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark checkup as completed
     *
     * @param PrenatalCheckup $checkup
     * @return PrenatalCheckup
     */
    public function markCompleted(PrenatalCheckup $checkup)
    {
        $checkup->update([
            'status' => 'completed',
            'conducted_by' => Auth::id(),
        ]);

        // Send SMS confirmation for completed checkup
        $this->sendCompletionSms($checkup);

        return $checkup;
    }

    /**
     * Send SMS confirmation for completed checkup
     */
    protected function sendCompletionSms($checkup)
    {
        $patient = $checkup->patient;
        if (!$patient || !$patient->contact) {
            return;
        }

        try {
            $smsService = new SmsService();
            $formattedDate = Carbon::parse($checkup->checkup_date)->format('F j, Y');

            $message = "Hello {$patient->name}! Your prenatal checkup on {$formattedDate} has been completed. ";
            if ($checkup->next_visit_date) {
                $nextDate = Carbon::parse($checkup->next_visit_date)->format('F j, Y');
                $message .= "Your next visit is scheduled on {$nextDate}. ";
            }
            $message .= "Thank you for your cooperation! - " . config('services.iprog.sender_name');

            $smsService->sendSms(
                $patient->contact,
                $message,
                'prenatal_checkup_completed',
                $patient->name,
                'PrenatalCheckup',
                $checkup->id
            );

            \Log::info('Prenatal checkup completion SMS sent', [
                'patient_id' => $patient->id,
                'checkup_id' => $checkup->id,
                'phone' => $patient->contact
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send prenatal checkup completion SMS: ' . $e->getMessage());
        }
    }

    /**
     * Mark checkup as missed
     *
     * @param PrenatalCheckup $checkup
     * @param string|null $reason
     * @return PrenatalCheckup
     */
    public function markAsMissed(PrenatalCheckup $checkup, $reason = null)
    {
        $checkup->update([
            'status' => 'missed',
            'missed_date' => now(),
            'missed_reason' => $reason,
            'auto_missed' => $reason === null, // Auto-missed if no reason provided
        ]);

        return $checkup;
    }

    /**
     * Reschedule a missed checkup
     *
     * @param PrenatalCheckup $checkup
     * @param string $newDate
     * @param string $newTime
     * @return PrenatalCheckup
     */
    public function rescheduleMissed(PrenatalCheckup $checkup, $newDate, $newTime)
    {
        DB::beginTransaction();

        try {
            // Create new checkup for the rescheduled date
            $newCheckup = PrenatalCheckup::create([
                'patient_id' => $checkup->patient_id,
                'prenatal_record_id' => $checkup->prenatal_record_id,
                'checkup_date' => $newDate,
                'checkup_time' => $newTime,
                'gestational_age_weeks' => $checkup->gestational_age_weeks,
                'status' => 'upcoming',
                'conducted_by' => Auth::id(),
            ]);

            // Update old checkup status to rescheduled
            $checkup->update([
                'status' => 'rescheduled',
            ]);

            // Send SMS for rescheduled checkup
            $patient = $checkup->patient;
            if ($patient) {
                $this->sendCheckupReminder($patient, $newCheckup);
            }

            DB::commit();

            return $newCheckup;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if checkup already exists for patient on date
     *
     * @param int $patientId
     * @param string $date
     * @param int|null $excludeCheckupId
     * @return bool
     */
    public function checkupExists($patientId, $date, $excludeCheckupId = null)
    {
        $query = PrenatalCheckup::where('patient_id', $patientId)
            ->whereDate('checkup_date', $date)
            ->where('status', 'done');

        if ($excludeCheckupId) {
            $query->where('id', '!=', $excludeCheckupId);
        }

        return $query->exists();
    }
}
