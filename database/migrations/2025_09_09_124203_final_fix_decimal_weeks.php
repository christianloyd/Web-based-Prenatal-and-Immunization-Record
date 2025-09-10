<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up()
    {
        echo "Final fix for all decimal weeks in prenatal_checkups table...\n";
        
        // Get all checkups that need fixing
        $checkups = DB::table('prenatal_checkups')
            ->join('patients', 'prenatal_checkups.patient_id', '=', 'patients.id')
            ->join('prenatal_records', function($join) {
                $join->on('patients.id', '=', 'prenatal_records.patient_id')
                     ->whereIn('prenatal_records.status', ['normal', 'monitor', 'high-risk', 'due']);
            })
            ->select(
                'prenatal_checkups.id',
                'prenatal_checkups.checkup_date',
                'prenatal_checkups.weeks_pregnant',
                'prenatal_records.last_menstrual_period'
            )
            ->whereNotNull('prenatal_records.last_menstrual_period')
            ->get();

        $fixed = 0;
        $errors = 0;

        foreach ($checkups as $checkup) {
            try {
                $lmp = Carbon::parse($checkup->last_menstrual_period);
                $checkupDate = Carbon::parse($checkup->checkup_date);
                
                // Calculate total days between LMP and checkup date
                $totalDays = $lmp->diffInDays($checkupDate);
                
                // Convert to whole weeks only (no decimals)
                $weeks = intval($totalDays / 7);
                
                // Format properly
                $weeksPregnant = $weeks == 1 ? "1 week" : "{$weeks} weeks";

                // Update the record
                DB::table('prenatal_checkups')
                    ->where('id', $checkup->id)
                    ->update([
                        'weeks_pregnant' => $weeksPregnant,
                        'updated_at' => Carbon::now()
                    ]);

                echo "Fixed checkup ID {$checkup->id}: '{$checkup->weeks_pregnant}' -> '{$weeksPregnant}'\n";
                $fixed++;
                
            } catch (Exception $e) {
                echo "Error fixing checkup ID {$checkup->id}: " . $e->getMessage() . "\n";
                $errors++;
            }
        }

        echo "\nFinal migration completed!\n";
        echo "Fixed: {$fixed} records\n";
        echo "Errors: {$errors} records\n";
    }

    public function down()
    {
        echo "Cannot reverse this migration - decimal precision has been permanently removed.\n";
    }
};