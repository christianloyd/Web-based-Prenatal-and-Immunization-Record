<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrate existing immunization records to use vaccine_id
     * This should run AFTER both immunizations and vaccines tables exist
     * and AFTER the vaccine_id column has been added to immunizations
     */
    public function up(): void
    {
        // Check if both tables exist before proceeding
        if (!Schema::hasTable('immunizations') || !Schema::hasTable('vaccines')) {
            throw new Exception('Required tables (immunizations, vaccines) do not exist');
        }

        // Check if vaccine_id column exists
        if (!Schema::hasColumn('immunizations', 'vaccine_id')) {
            throw new Exception('vaccine_id column does not exist in immunizations table');
        }

        // Get all immunization records with vaccine_name but no vaccine_id
        $immunizations = DB::table('immunizations')
            ->whereNull('vaccine_id')
            ->whereNotNull('vaccine_name')
            ->get();

        echo "Found " . $immunizations->count() . " immunization records to migrate\n";

        foreach ($immunizations as $immunization) {
            // Try to find matching vaccine by exact name first
            $vaccine = DB::table('vaccines')
                ->where('name', $immunization->vaccine_name)
                ->first();

            if (!$vaccine) {
                // Try partial match if exact match fails
                $vaccine = DB::table('vaccines')
                    ->where('name', 'LIKE', '%' . $immunization->vaccine_name . '%')
                    ->first();
            }

            if ($vaccine) {
                // Update immunization with vaccine_id
                DB::table('immunizations')
                    ->where('id', $immunization->id)
                    ->update(['vaccine_id' => $vaccine->id]);
                
                echo "Linked immunization ID {$immunization->id} to vaccine '{$vaccine->name}'\n";
            } else {
                // Create vaccine record for vaccines that don't exist
                echo "Creating new vaccine record for '{$immunization->vaccine_name}'\n";
                
                $vaccineId = DB::table('vaccines')->insertGetId([
                    'name' => $immunization->vaccine_name,
                    'category' => 'Routine Immunization', // Default category
                    'dosage' => '0.5ml', // Default dosage
                    'current_stock' => 0, // Start with 0 stock
                    'min_stock' => 10,
                    'expiry_date' => now()->addYear(), // Default expiry 1 year from now
                    'storage_temp' => '2-8°C',
                    'notes' => 'Auto-created during migration from existing immunization record',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Update immunization with new vaccine_id
                DB::table('immunizations')
                    ->where('id', $immunization->id)
                    ->update(['vaccine_id' => $vaccineId]);

                echo "Created vaccine ID {$vaccineId} and linked to immunization ID {$immunization->id}\n";
            }
        }

        echo "Data migration completed successfully\n";
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        // Set vaccine_id to null for all records
        DB::table('immunizations')->update(['vaccine_id' => null]);
        
        // Optionally delete auto-created vaccines
        DB::table('vaccines')
            ->where('notes', 'LIKE', '%Auto-created during migration%')
            ->delete();
            
        echo "Data migration reversed\n";
    }
};