<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Notifications\HealthcareNotification;
use Illuminate\Support\Facades\Cache;

class PatientService
{
    /**
     * Create a new patient record
     */
    public function createPatient(array $data)
    {
        try {
            // Check for duplicate patient
            if ($this->patientExists($data['first_name'], $data['last_name'], $data['age'])) {
                throw new \Exception('A patient with the same name and age already exists.');
            }

            // Format phone numbers
            $data['contact'] = $this->formatPhoneNumber($data['contact']);
            $data['emergency_contact'] = $this->formatPhoneNumber($data['emergency_contact']);

            // Combine first_name and last_name to create name field
            $data['name'] = $data['first_name'] . ' ' . $data['last_name'];

            // Create the patient record
            $patient = Patient::create($data);

            // Send notifications
            $this->notifyPatientCreated($patient);

            return $patient;

        } catch (\Exception $e) {
            Log::error('Patient registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing patient record
     */
    public function updatePatient(Patient $patient, array $data)
    {
        try {
            // Check for duplicate patient (excluding current patient)
            if ($this->patientExists($data['first_name'], $data['last_name'], $data['age'], $patient->id)) {
                throw new \Exception('Another patient with the same name and age already exists.');
            }

            // Format phone numbers
            $data['contact'] = $this->formatPhoneNumber($data['contact']);
            $data['emergency_contact'] = $this->formatPhoneNumber($data['emergency_contact']);

            // Combine first_name and last_name to create name field
            $data['name'] = $data['first_name'] . ' ' . $data['last_name'];

            // Update the patient record
            $patient->update($data);

            return $patient;

        } catch (\Exception $e) {
            Log::error('Patient update failed', [
                'patient_id' => $patient->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Delete a patient (only if no prenatal records)
     */
    public function deletePatient(Patient $patient)
    {
        try {
            // Check if patient has prenatal records
            if ($patient->prenatalRecords()->count() > 0) {
                throw new \Exception('Cannot delete patient with existing prenatal records.');
            }

            $patientName = $patient->name;
            $patient->delete();

            return $patientName;

        } catch (\Exception $e) {
            Log::error('Error deleting patient', [
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check if patient already exists
     */
    public function patientExists($firstName, $lastName, $age, $excludeId = null)
    {
        $query = Patient::where('first_name', 'LIKE', $firstName)
            ->where('last_name', 'LIKE', $lastName)
            ->where('age', $age);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
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
     * Send notifications about new patient registration
     */
    private function notifyPatientCreated(Patient $patient)
    {
        // If BHW is registering, send high-priority notification to midwives
        if (Auth::user()->role === 'bhw') {
            $this->notifyMidwivesOfBHWAction(
                'New Patient Registered',
                "registered a new patient '{$patient->name}' in the system.",
                'success',
                route('midwife.patients.show', $patient->id),
                ['patient_id' => $patient->id, 'action' => 'patient_registered', 'patient_name' => $patient->name]
            );
        }

        // Also send regular notification to all healthcare workers
        $this->notifyHealthcareWorkers(
            'New Patient Registered',
            "A new patient '{$patient->name}' has been registered in the system.",
            'success',
            Auth::user()->role === 'midwife'
                ? route('midwife.patients.show', $patient->id)
                : route('bhw.patients.show', $patient->id),
            ['patient_id' => $patient->id, 'action' => 'patient_registered']
        );
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

    /**
     * Send high-priority notification to midwives about BHW actions
     */
    private function notifyMidwivesOfBHWAction($title, $messageFragment, $type, $actionUrl, $data)
    {
        $midwives = User::where('role', 'midwife')->get();

        $message = Auth::user()->name . ' (BHW) ' . $messageFragment;

        foreach ($midwives as $midwife) {
            $midwife->notify(new HealthcareNotification(
                $title,
                $message,
                $type,
                $actionUrl,
                array_merge($data, [
                    'notified_by' => Auth::user()->name,
                    'notified_by_role' => 'bhw',
                    'priority' => 'high'
                ])
            ));

            Cache::forget("unread_notifications_count_{$midwife->id}");
            Cache::forget("recent_notifications_{$midwife->id}");
        }
    }
}
