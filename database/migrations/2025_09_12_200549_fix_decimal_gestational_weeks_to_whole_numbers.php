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
     *
     * @return void
     */
    public function up()
    {
        // Get all prenatal records that have LMP data
        $records = DB::table('prenatal_records')
            ->whereNotNull('last_menstrual_period')
            ->whereNull('deleted_at')
            ->get();

        echo "Found " . count($records) . " prenatal records to update for decimal weeks fix...\n";

        foreach ($records as $record) {
            if ($record->last_menstrual_period) {
                try {
                    $lmp = Carbon::parse($record->last_menstrual_period);
                    $totalDays = $lmp->diffInDays(Carbon::now());
                    
                    // Calculate weeks and days (ensure whole numbers)
                    $weeks = intval($totalDays / 7);
                    $days = $totalDays % 7;
                    
                    // Format gestational age with whole numbers only
                    if ($weeks == 0) {
                        $gestationalAge = $days == 1 ? "1 day" : "{$days} days";
                    } elseif ($days == 0) {
                        $gestationalAge = $weeks == 1 ? "1 week" : "{$weeks} weeks";
                    } else {
                        $weekText = $weeks == 1 ? "1 week" : "{$weeks} weeks";
                        $dayText = $days == 1 ? "1 day" : "{$days} days";
                        $gestationalAge = "{$weekText} {$dayText}";
                    }

                    // Calculate trimester based on whole weeks
                    $trimester = $weeks <= 12 ? 1 : ($weeks <= 26 ? 2 : 3);

                    // Update the record
                    DB::table('prenatal_records')
                        ->where('id', $record->id)
                        ->update([
                            'gestational_age' => $gestationalAge,
                            'trimester' => $trimester,
                            'updated_at' => Carbon::now()
                        ]);

                    echo "Fixed record ID {$record->id}: {$gestationalAge} (was: {$record->gestational_age})\n";
                    
                } catch (Exception $e) {
                    echo "Error updating record ID {$record->id}: " . $e->getMessage() . "\n";
                }
            }
        }

        echo "Decimal weeks fix migration completed!\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        echo "This migration cannot be easily reversed as it fixes decimal week calculations.\n";
        echo "If you need to revert, you would need to recalculate from the last_menstrual_period field.\n";
    }
};
