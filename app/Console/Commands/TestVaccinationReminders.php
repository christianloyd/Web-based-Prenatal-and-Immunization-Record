<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Immunization;
use App\Services\NotificationService;
use Carbon\Carbon;

class TestVaccinationReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:vaccination-reminders {date? : The target date to test (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test vaccination reminders for a specific date (simulates that tomorrow is the given date)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDate = $this->argument('date') ?: Carbon::tomorrow()->toDateString();
        $this->info("Simulating vaccination reminders for target date: {$targetDate}");

        try {
            $upcomingImmunizations = Immunization::with(['childRecord.mother', 'vaccine'])
                ->whereDate('schedule_date', $targetDate)
                ->where('status', 'Upcoming')
                ->get();

            if ($upcomingImmunizations->isEmpty()) {
                $this->warn("No upcoming vaccinations found for {$targetDate}.");
                return 0;
            }

            $this->info("Found {$upcomingImmunizations->count()} vaccinations for {$targetDate}. Sending reminders...");

            foreach ($upcomingImmunizations as $immunization) {
                $childName = $immunization->childRecord->full_name ?? 'Unknown Child';
                $vaccineName = optional($immunization->vaccine)->name ?? $immunization->vaccine_name ?? 'Vaccine';
                
                $this->line("-> Sending reminder for: {$childName} ({$vaccineName})");
                
                // We call the existing service to send the reminder
                NotificationService::sendUpcomingImmunizationReminder($immunization);
            }

            $this->info('Test reminders completed successfully!');
        } catch (\Exception $e) {
            $this->error('Error during test: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
