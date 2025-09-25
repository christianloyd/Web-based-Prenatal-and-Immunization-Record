<?php

namespace App\Console\Commands;

use App\Models\PrenatalCheckup;
use App\Models\Notification;
use Illuminate\Console\Command;

class MarkTodaysMissedCheckups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkups:mark-todays-missed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark today\'s uncompleted prenatal checkups as missed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to check for missed prenatal checkups...');

        // Get today's scheduled checkups that haven't been completed
        $missedCheckups = PrenatalCheckup::where('status', 'scheduled')
            ->whereDate('checkup_date', today())
            ->with('patient')
            ->get();

        if ($missedCheckups->isEmpty()) {
            $this->info('No missed checkups found for today.');
            return 0;
        }

        $this->info("Found {$missedCheckups->count()} checkups to mark as missed...");

        foreach ($missedCheckups as $checkup) {
            // Update checkup status
            $checkup->update([
                'status' => 'missed',
                'missed_date' => now(),
                'auto_missed' => true,
                'missed_reason' => 'Did not show up for scheduled appointment'
            ]);

            // Create notification for the midwife/BHW who created the checkup
            if ($checkup->created_by) {
                Notification::create([
                    'user_id' => $checkup->created_by,
                    'type' => 'missed_checkup',
                    'title' => 'Missed Prenatal Checkup',
                    'message' => "Patient {$checkup->patient->name} missed checkup scheduled for " . $checkup->checkup_date->format('M j, Y'),
                    'data' => json_encode([
                        'checkup_id' => $checkup->id,
                        'patient_id' => $checkup->patient_id,
                        'checkup_date' => $checkup->checkup_date->format('Y-m-d')
                    ])
                ]);
            }

            $this->line("Marked checkup for {$checkup->patient->name} as missed");
        }

        $this->info("Successfully marked {$missedCheckups->count()} checkups as missed for today.");
        return 0;
    }
}
