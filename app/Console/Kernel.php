<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run notification checks daily at 8:00 AM
        $schedule->command('notifications:check')
                 ->dailyAt('08:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Run notification checks again at 2:00 PM for appointments
        $schedule->command('notifications:check')
                 ->dailyAt('14:00') 
                 ->withoutOverlapping()
                 ->runInBackground();

        // Check for backup reminders weekly on Monday at 9:00 AM
        $schedule->call(function () {
            \App\Services\NotificationService::sendBackupReminder();
        })->weekly()->mondays()->at('09:00');

        // Mark today's missed prenatal checkups at 5 PM (end of business day)
        $schedule->command('checkups:mark-todays-missed')
                 ->dailyAt('17:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Also run at end of day as backup at 11:59 PM
        $schedule->command('checkups:mark-todays-missed')
                 ->dailyAt('23:59')
                 ->withoutOverlapping()
                 ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}