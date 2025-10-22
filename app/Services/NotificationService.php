<?php

namespace App\Services;

use App\Models\User;
use App\Models\Patient;
use App\Models\ChildRecord;
use App\Models\PrenatalCheckup;
use App\Models\Vaccine;
use App\Notifications\HealthcareNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Clear notification cache for a user
     */
    private static function clearUserNotificationCache($userId)
    {
        Cache::forget("unread_notifications_count_{$userId}");
        Cache::forget("recent_notifications_{$userId}");
    }
    /**
     * Send appointment confirmation (when created) - IMMEDIATE SMS
     * Sends for NEXT VISIT DATE, not current checkup date
     */
    public static function sendAppointmentConfirmation(PrenatalCheckup $checkup)
    {
        try {
            $patient = $checkup->prenatalRecord->patient ?? null;
            $patientName = $patient ? $patient->full_name : 'Unknown Patient';

            // Use NEXT VISIT DATE for SMS (not current checkup date)
            $appointmentDate = $checkup->next_visit_date ?? $checkup->checkup_date;

            // Send SMS confirmation to patient
            if ($patient && !empty($patient->contact)) {
                $patient->notify(new HealthcareNotification(
                    'Next Appointment Scheduled',
                    "Hi {$patientName}! Your next prenatal checkup has been scheduled for " .
                    \Carbon\Carbon::parse($appointmentDate)->format('F d, Y') . ". You will receive a reminder 1 day before. - HealthCare System",
                    'info',
                    null,
                    [
                        'checkup_id' => $checkup->id,
                        'next_visit_date' => $appointmentDate,
                        'type' => 'confirmation'
                    ],
                    true // Enable SMS
                ));

                Log::info("SMS confirmation sent to patient: {$patientName} ({$patient->contact}) for next visit: {$appointmentDate}");
            }

            // Send in-app notification to midwives
            $midwives = User::where('role', 'Midwife')->where('is_active', true)->get();
            foreach ($midwives as $midwife) {
                $midwife->notify(new HealthcareNotification(
                    'New Appointment Scheduled',
                    "Prenatal checkup scheduled for {$patientName} on " . $checkup->checkup_date->format('M d, Y'),
                    'info',
                    route('midwife.prenatalcheckup.index'),
                    [
                        'checkup_id' => $checkup->id,
                        'patient_id' => $checkup->prenatal_record_id,
                        'checkup_date' => $checkup->checkup_date->toDateString()
                    ],
                    false // No SMS for staff
                ));
                self::clearUserNotificationCache($midwife->id);
            }

            Log::info("Appointment confirmation sent for checkup ID: {$checkup->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send appointment confirmation: " . $e->getMessage());
        }
    }

    /**
     * Send appointment reminder (1 day before) - SCHEDULED SMS
     * Sends for NEXT VISIT DATE
     */
    public static function sendAppointmentReminder(PrenatalCheckup $checkup)
    {
        try {
            $patient = $checkup->prenatalRecord->patient ?? null;
            $patientName = $patient ? $patient->full_name : 'Unknown Patient';

            // Use NEXT VISIT DATE for reminder
            $appointmentDate = $checkup->next_visit_date ?? $checkup->checkup_date;

            // Send SMS reminder to patient
            if ($patient && !empty($patient->contact)) {
                $patient->notify(new HealthcareNotification(
                    'Appointment Reminder',
                    "REMINDER: Hi {$patientName}! Your prenatal checkup is TOMORROW, " .
                    \Carbon\Carbon::parse($appointmentDate)->format('F d, Y') . ". Please arrive on time. - HealthCare System",
                    'warning',
                    null,
                    [
                        'checkup_id' => $checkup->id,
                        'next_visit_date' => $appointmentDate,
                        'type' => 'reminder'
                    ],
                    true // Enable SMS
                ));

                Log::info("SMS reminder sent to patient: {$patientName} ({$patient->contact}) for next visit: {$appointmentDate}");
            }

            // Send in-app notification to midwives
            $midwives = User::where('role', 'Midwife')->where('is_active', true)->get();
            foreach ($midwives as $midwife) {
                $midwife->notify(new HealthcareNotification(
                    'Appointment Reminder - Tomorrow',
                    "Prenatal checkup for {$patientName} is tomorrow " . \Carbon\Carbon::parse($appointmentDate)->format('M d, Y'),
                    'warning',
                    route('midwife.prenatalcheckup.index'),
                    [
                        'checkup_id' => $checkup->id,
                        'patient_id' => $checkup->prenatal_record_id,
                        'next_visit_date' => $appointmentDate
                    ],
                    false // No SMS for staff
                ));
                self::clearUserNotificationCache($midwife->id);
            }

            Log::info("Appointment reminder sent for checkup ID: {$checkup->id} for next visit: {$appointmentDate}");
        } catch (\Exception $e) {
            Log::error("Failed to send appointment reminder: " . $e->getMessage());
        }
    }

    /**
     * Send vaccination reminder notification
     */
    public static function sendVaccinationReminder(ChildRecord $child)
    {
        try {
            // Find all midwives to notify
            $midwives = User::where('role', 'Midwife')->where('is_active', true)->get();

            // Send notification to midwives (in-app only)
            foreach ($midwives as $midwife) {
                $midwife->notify(new HealthcareNotification(
                    'Vaccination Due Reminder',
                    "Child {$child->full_name} has upcoming vaccinations due",
                    'warning',
                    route('midwife.childrecord.show', $child->id),
                    [
                        'child_id' => $child->id,
                        'child_name' => $child->full_name,
                        'birth_date' => $child->date_of_birth
                    ],
                    false // No SMS for staff
                ));

                // Clear notification cache for the recipient
                self::clearUserNotificationCache($midwife->id);
            }

            // Send SMS to the mother/guardian if contact is available
            $mother = $child->mother ?? null;
            if ($mother && !empty($mother->contact)) {
                $mother->notify(new HealthcareNotification(
                    'Vaccination Reminder',
                    "Hi! Your child {$child->full_name} is due for vaccination. Please visit the health center soon. - HealthCare System",
                    'warning',
                    null,
                    [
                        'child_id' => $child->id,
                        'child_name' => $child->full_name
                    ],
                    true // Enable SMS for mother
                ));

                Log::info("SMS vaccination reminder sent to mother of {$child->full_name} ({$mother->contact})");
            }

            Log::info("Vaccination reminder sent for child ID: {$child->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send vaccination reminder: " . $e->getMessage());
        }
    }

    /**
     * Send low vaccine stock alert
     */
    public static function sendLowStockAlert(Vaccine $vaccine)
    {
        try {
            // Find all midwives to notify
            $midwives = User::where('role', 'Midwife')->where('is_active', true)->get();
            
            foreach ($midwives as $midwife) {
                $midwife->notify(new HealthcareNotification(
                    'Low Vaccine Stock Alert',
                    "{$vaccine->vaccine_name} stock is running low. Current stock: {$vaccine->stock_quantity} vials",
                    'warning',
                    route('midwife.vaccines.index'),
                    [
                        'vaccine_id' => $vaccine->id,
                        'vaccine_name' => $vaccine->vaccine_name,
                        'current_stock' => $vaccine->stock_quantity,
                        'minimum_threshold' => $vaccine->minimum_threshold ?? 10
                    ]
                ));
                
                // Clear notification cache for the recipient
                self::clearUserNotificationCache($midwife->id);
            }
            
            Log::info("Low stock alert sent for vaccine: {$vaccine->vaccine_name}");
        } catch (\Exception $e) {
            Log::error("Failed to send low stock alert: " . $e->getMessage());
        }
    }

    /**
     * Send new patient registration notification
     */
    public static function sendNewPatientNotification(Patient $patient)
    {
        try {
            // Find all midwives and BHWs to notify
            $users = User::whereIn('role', ['Midwife', 'BHW'])->where('is_active', true)->get();
            
            foreach ($users as $user) {
                $user->notify(new HealthcareNotification(
                    'New Patient Registered',
                    "New patient {$patient->full_name} has been registered in the system",
                    'info',
                    $user->role === 'midwife' ? route('midwife.patients.index') : route('bhw.patients.index'),
                    [
                        'patient_id' => $patient->id,
                        'patient_name' => $patient->full_name,
                        'registration_date' => $patient->created_at->toDateString()
                    ]
                ));
                
                // Clear notification cache for the recipient
                self::clearUserNotificationCache($user->id);
            }
            
            Log::info("New patient notification sent for: {$patient->full_name}");
        } catch (\Exception $e) {
            Log::error("Failed to send new patient notification: " . $e->getMessage());
        }
    }

    /**
     * Send cloud backup reminder
     */
    public static function sendBackupReminder()
    {
        try {
            // Find all midwives to notify
            $midwives = User::where('role', 'Midwife')->where('is_active', true)->get();
            
            foreach ($midwives as $midwife) {
                $midwife->notify(new HealthcareNotification(
                    'Cloud Backup Reminder',
                    'It has been a while since your last backup. Consider backing up your healthcare data to ensure safety.',
                    'warning',
                    route('midwife.cloudbackup.index'),
                    [
                        'backup_type' => 'scheduled_reminder',
                        'reminder_date' => now()->toDateString()
                    ]
                ));
                
                // Clear notification cache for the recipient
                self::clearUserNotificationCache($midwife->id);
            }
            
            Log::info("Backup reminder sent to all midwives");
        } catch (\Exception $e) {
            Log::error("Failed to send backup reminder: " . $e->getMessage());
        }
    }

    /**
     * Send system maintenance notification
     */
    public static function sendMaintenanceNotification($title, $message, $type = 'info')
    {
        try {
            // Notify all active users
            $users = User::where('is_active', true)->get();
            
            foreach ($users as $user) {
                $user->notify(new HealthcareNotification(
                    $title,
                    $message,
                    $type,
                    '/dashboard',
                    [
                        'maintenance_type' => 'system_notification',
                        'notification_date' => now()->toDateString()
                    ]
                ));
                
                // Clear notification cache for the recipient
                self::clearUserNotificationCache($user->id);
            }
            
            Log::info("System maintenance notification sent to all users: {$title}");
        } catch (\Exception $e) {
            Log::error("Failed to send maintenance notification: " . $e->getMessage());
        }
    }

    /**
     * Check for upcoming appointments and send reminders
     * Checks NEXT VISIT DATE (not current checkup date)
     */
    public static function checkUpcomingAppointments()
    {
        try {
            $tomorrow = Carbon::tomorrow();

            // Find appointments with NEXT VISIT DATE tomorrow
            $upcomingCheckups = PrenatalCheckup::where('next_visit_date', $tomorrow->toDateString())
                ->whereNotNull('next_visit_date')
                ->with(['prenatalRecord.patient'])
                ->get();

            foreach ($upcomingCheckups as $checkup) {
                self::sendAppointmentReminder($checkup);
            }

            Log::info("Checked upcoming appointments (next visit): " . $upcomingCheckups->count() . " reminders sent");
        } catch (\Exception $e) {
            Log::error("Failed to check upcoming appointments: " . $e->getMessage());
        }
    }

    /**
     * Check for children due for vaccinations
     */
    public static function checkVaccinationsDue()
    {
        try {
            // Get children who might be due for vaccinations
            // This is a simplified check - in reality, you'd have a more complex vaccination schedule
            $children = ChildRecord::where('date_of_birth', '>=', Carbon::now()->subYears(2))
                ->with(['immunizations'])
                ->get();

            $childrenNeedingVaccination = $children->filter(function ($child) {
                // Simple logic: if child has fewer than expected immunizations for their age
                $ageInMonths = Carbon::parse($child->date_of_birth)->diffInMonths(Carbon::now());
                $expectedImmunizations = min(floor($ageInMonths / 2), 12); // Rough estimate
                
                return $child->immunizations->count() < $expectedImmunizations;
            });

            foreach ($childrenNeedingVaccination as $child) {
                self::sendVaccinationReminder($child);
            }

            Log::info("Checked vaccination schedules: " . $childrenNeedingVaccination->count() . " reminders sent");
        } catch (\Exception $e) {
            Log::error("Failed to check vaccination schedules: " . $e->getMessage());
        }
    }

    /**
     * Check for low vaccine stocks
     */
    public static function checkLowVaccineStock()
    {
        try {
            $lowStockVaccines = Vaccine::where('stock_quantity', '<=', 10) // Threshold of 10 vials
                ->where('is_active', true)
                ->get();

            foreach ($lowStockVaccines as $vaccine) {
                self::sendLowStockAlert($vaccine);
            }

            Log::info("Checked vaccine stocks: " . $lowStockVaccines->count() . " low stock alerts sent");
        } catch (\Exception $e) {
            Log::error("Failed to check vaccine stocks: " . $e->getMessage());
        }
    }
}