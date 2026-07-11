<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class CheckVaccinations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:vaccinations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for due vaccinations and low vaccine stocks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting vaccination notification checks...');

        try {
            // Check for vaccinations due
            $this->info('Checking vaccination schedules...');
            NotificationService::checkVaccinationsDue();

            // Check for low vaccine stocks
            $this->info('Checking vaccine stock levels...');
            NotificationService::checkLowVaccineStock();

            $this->info('Vaccination checks completed successfully!');
        } catch (\Exception $e) {
            $this->error('Error during vaccination checks: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
