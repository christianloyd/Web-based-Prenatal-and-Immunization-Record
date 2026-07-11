<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrenatalCheckup;
use App\Services\NotificationService;
use Carbon\Carbon;

class TestPrenatalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:prenatal-reminders {date? : The target date to test (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test prenatal reminders for a specific date (simulates that tomorrow is the given date)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDate = $this->argument('date') ?: Carbon::tomorrow()->toDateString();
        $this->info("Simulating prenatal reminders for target date: {$targetDate}");

        try {
            $reminders = collect();
            $deduplicationKeys = [];

            // Upcoming checkups scheduled for the target date based on the actual checkup date
            $upcomingCheckups = PrenatalCheckup::whereDate('checkup_date', $targetDate)
                ->where('status', 'upcoming')
                ->with(['prenatalRecord.patient'])
                ->get();

            foreach ($upcomingCheckups as $checkup) {
                $key = 'patient:' . ($checkup->patient_id ?? ('checkup-' . $checkup->id)) . '|date:' . $targetDate;
                $deduplicationKeys[$key] = true;
                $reminders->push($checkup);
            }

            // Legacy records that still rely on next_visit_date for scheduling
            $legacyCheckups = PrenatalCheckup::whereDate('next_visit_date', $targetDate)
                ->whereNotNull('next_visit_date')
                ->with(['prenatalRecord.patient'])
                ->get();

            foreach ($legacyCheckups as $checkup) {
                $key = 'patient:' . ($checkup->patient_id ?? ('checkup-' . $checkup->id)) . '|date:' . $targetDate;
                if (!isset($deduplicationKeys[$key])) {
                    $deduplicationKeys[$key] = true;
                    $reminders->push($checkup);
                }
            }

            if ($reminders->isEmpty()) {
                $this->warn("No upcoming appointments found for {$targetDate}.");
                return 0;
            }

            $this->info("Found {$reminders->count()} appointments for {$targetDate}. Sending reminders...");

            foreach ($reminders as $checkup) {
                $patientName = $checkup->prenatalRecord->patient->full_name ?? ($checkup->prenatalRecord->patient->name ?? 'Unknown Patient');
                $this->line("-> Sending reminder for: {$patientName} (Checkup ID: {$checkup->id})");
                
                // We call the existing service to send the reminder
                NotificationService::sendAppointmentReminder($checkup);
            }

            $this->info('Test reminders completed successfully!');
        } catch (\Exception $e) {
            $this->error('Error during test: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
