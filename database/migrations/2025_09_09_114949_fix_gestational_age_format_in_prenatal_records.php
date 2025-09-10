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

        echo "Found " . count($records) . " prenatal records to update...\n";

        foreach ($records as $record) {
            if ($record->last_menstrual_period) {
                try {
                    $lmp = Carbon::parse($record->last_menstrual_period);
                    $totalDays = $lmp->diffInDays(Carbon::now());
                    
                    // Calculate weeks and days
                    $weeks = intval($totalDays / 7);
                    $days = $totalDays % 7;
                    
                    // Format gestational age
                    if ($weeks == 0) {
                        $gestationalAge = $days == 1 ? "1 day" : "{$days} days";
                    } elseif ($days == 0) {
                        $gestationalAge = $weeks == 1 ? "1 week" : "{$weeks} weeks";
                    } else {
                        $weekText = $weeks == 1 ? "1 week" : "{$weeks} weeks";
                        $dayText = $days == 1 ? "1 day" : "{$days} days";
                        $gestationalAge = "{$weekText} {$dayText}";
                    }

                    // Update the record
                    DB::table('prenatal_records')
                        ->where('id', $record->id)
                        ->update([
                            'gestational_age' => $gestationalAge,
                            'trimester' => $weeks <= 12 ? 1 : ($weeks <= 26 ? 2 : 3),
                            'updated_at' => Carbon::now()
                        ]);

                    echo "Updated record ID {$record->id}: {$gestationalAge}\n";
                    
                } catch (Exception $e) {
                    echo "Error updating record ID {$record->id}: " . $e->getMessage() . "\n";
                }
            }
        }

        echo "Migration completed!\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // We can't easily reverse this without losing precision
        // If needed, we could recalculate from LMP again
        echo "This migration cannot be easily reversed as it converts decimal weeks to a more readable format.\n";
        echo "If you need to revert, you would need to recalculate from the last_menstrual_period field.\n";
    }
};