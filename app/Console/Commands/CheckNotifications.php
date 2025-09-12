<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class CheckNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for upcoming appointments, due vaccinations, and low vaccine stocks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting notification checks...');

        try {
            // Check for upcoming appointments
            $this->info('Checking upcoming appointments...');
            NotificationService::checkUpcomingAppointments();

            // Check for vaccinations due
            $this->info('Checking vaccination schedules...');
            NotificationService::checkVaccinationsDue();

            // Check for low vaccine stocks
            $this->info('Checking vaccine stock levels...');
            NotificationService::checkLowVaccineStock();

            $this->info('Notification checks completed successfully!');
        } catch (\Exception $e) {
            $this->error('Error during notification checks: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}