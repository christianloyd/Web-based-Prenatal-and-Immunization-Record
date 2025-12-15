<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Extend enum definition when running on MySQL-compatible drivers
            \DB::statement("ALTER TABLE prenatal_checkups MODIFY COLUMN status ENUM('upcoming', 'done', 'scheduled', 'missed', 'completed', 'cancelled', 'rescheduled') NOT NULL DEFAULT 'upcoming'");
        }

        // Update existing records to align with the new status naming
        \DB::statement("UPDATE prenatal_checkups SET status = 'scheduled' WHERE status = 'upcoming'");
        \DB::statement("UPDATE prenatal_checkups SET status = 'completed' WHERE status = 'done'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Revert status values back to original nomenclature
        \DB::statement("UPDATE prenatal_checkups SET status = 'upcoming' WHERE status IN ('scheduled', 'missed', 'rescheduled')");
        \DB::statement("UPDATE prenatal_checkups SET status = 'done' WHERE status IN ('completed', 'cancelled')");

        if ($driver === 'mysql') {
            // Restore enum definition for MySQL-compatible drivers
            \DB::statement("ALTER TABLE prenatal_checkups MODIFY COLUMN status ENUM('upcoming', 'done') NOT NULL DEFAULT 'upcoming'");
        }
    }
};
