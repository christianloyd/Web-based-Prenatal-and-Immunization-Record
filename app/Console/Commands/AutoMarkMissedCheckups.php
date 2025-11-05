<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PrenatalCheckup;
use Carbon\Carbon;

class AutoMarkMissedCheckups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkups:auto-mark-missed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark prenatal checkups as missed if the appointment date has passed (runs at 5 PM PH Time)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Set timezone to Philippine Time
        $now = Carbon::now('Asia/Manila');
        $today = $now->toDateString();

        $this->info("Running auto-mark missed checkups at {$now->format('Y-m-d H:i:s')} (PH Time)");

        // Find all upcoming checkups where the checkup_date has passed
        $missedCheckups = PrenatalCheckup::where('status', 'upcoming')
            ->where('checkup_date', '<', $today)
            ->get();

        $count = 0;

        foreach ($missedCheckups as $checkup) {
            // Mark as missed
            $checkup->status = 'missed';
            $checkup->auto_missed = true;
            $checkup->missed_date = $now;
            $checkup->missed_reason = 'Automatically marked as missed - appointment date passed';
            $checkup->save();

            $count++;

            $this->line("Marked checkup ID {$checkup->id} (Date: {$checkup->checkup_date->format('Y-m-d')}) as missed");
        }

        if ($count > 0) {
            $this->info("Successfully marked {$count} checkup(s) as missed.");
        } else {
            $this->info("No checkups to mark as missed.");
        }

        return Command::SUCCESS;
    }
}
