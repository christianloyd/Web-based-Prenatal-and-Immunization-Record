<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Get all prenatal checkups
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

        echo "Found " . count($checkups) . " checkups to update...\n";

        foreach ($checkups as $checkup) {
            try {
                $lmp = Carbon::parse($checkup->last_menstrual_period);
                $checkupDate = Carbon::parse($checkup->checkup_date);
                $totalDays = $lmp->diffInDays($checkupDate);
                
                // Calculate weeks only (no days)
                $weeks = intval($totalDays / 7);
                
                // Format weeks pregnant (weeks only)
                $weeksPregnant = $weeks == 1 ? "1 week" : "{$weeks} weeks";

                // Update the checkup
                DB::table('prenatal_checkups')
                    ->where('id', $checkup->id)
                    ->update([
                        'weeks_pregnant' => $weeksPregnant,
                        'updated_at' => Carbon::now()
                    ]);

                echo "Updated checkup ID {$checkup->id}: {$weeksPregnant}\n";
                
            } catch (Exception $e) {
                echo "Error updating checkup ID {$checkup->id}: " . $e->getMessage() . "\n";
            }
        }

        echo "Migration completed!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        echo "This migration cannot be easily reversed.\n";
        echo "The weeks_pregnant field format has been standardized to show weeks only.\n";
    }
};