<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class CheckPrenatalAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:prenatal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for upcoming prenatal appointments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting prenatal appointment notification checks...');

        try {
            // Check for upcoming appointments
            $this->info('Checking upcoming appointments...');
            NotificationService::checkUpcomingAppointments();

            $this->info('Prenatal appointment checks completed successfully!');
        } catch (\Exception $e) {
            $this->error('Error during prenatal appointment checks: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
