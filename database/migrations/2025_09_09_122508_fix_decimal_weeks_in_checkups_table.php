<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up()
    {
        echo "Fixing existing decimal weeks in prenatal_checkups table...\n";
        
        // Get all checkups with weeks_pregnant data
        $checkups = DB::table('prenatal_checkups')
            ->whereNotNull('weeks_pregnant')
            ->get();

        $fixed = 0;
        $errors = 0;

        foreach ($checkups as $checkup) {
            try {
                $weeksString = $checkup->weeks_pregnant;
                
                // Skip if already in correct format (no decimal)
                if (!strpos($weeksString, '.')) {
                    continue;
                }
                
                // Extract decimal number from strings like "40.359391420938 weeks"
                preg_match('/(\d+\.?\d*)\s*weeks?/', $weeksString, $matches);
                
                if (isset($matches[1])) {
                    $weeksFloat = floatval($matches[1]);
                    $wholeWeeks = intval($weeksFloat);
                    
                    // Format properly
                    $newWeeksPregnant = $wholeWeeks == 1 ? "1 week" : "{$wholeWeeks} weeks";

                    // Update the record
                    DB::table('prenatal_checkups')
                        ->where('id', $checkup->id)
                        ->update([
                            'weeks_pregnant' => $newWeeksPregnant,
                            'updated_at' => Carbon::now()
                        ]);

                    echo "Fixed checkup ID {$checkup->id}: '{$weeksString}' -> '{$newWeeksPregnant}'\n";
                    $fixed++;
                } else {
                    echo "Could not parse weeks from: '{$weeksString}' (ID: {$checkup->id})\n";
                }
                
            } catch (Exception $e) {
                echo "Error fixing checkup ID {$checkup->id}: " . $e->getMessage() . "\n";
                $errors++;
            }
        }

        echo "\nMigration completed!\n";
        echo "Fixed: {$fixed} records\n";
        echo "Errors: {$errors} records\n";
    }

    public function down()
    {
        echo "Cannot reverse this migration - decimal precision has been removed.\n";
    }
};