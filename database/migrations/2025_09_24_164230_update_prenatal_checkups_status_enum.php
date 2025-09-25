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
        // Use DB statement to alter the enum column to include new values
        \DB::statement("ALTER TABLE prenatal_checkups MODIFY COLUMN status ENUM('upcoming', 'done', 'scheduled', 'missed', 'completed', 'cancelled', 'rescheduled') NOT NULL DEFAULT 'upcoming'");

        // Update existing 'upcoming' records to 'scheduled' for consistency
        \DB::statement("UPDATE prenatal_checkups SET status = 'scheduled' WHERE status = 'upcoming'");

        // Update existing 'done' records to 'completed' for consistency
        \DB::statement("UPDATE prenatal_checkups SET status = 'completed' WHERE status = 'done'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status values back to original
        \DB::statement("UPDATE prenatal_checkups SET status = 'upcoming' WHERE status IN ('scheduled', 'missed', 'rescheduled')");
        \DB::statement("UPDATE prenatal_checkups SET status = 'done' WHERE status IN ('completed', 'cancelled')");

        // Revert enum back to original values
        \DB::statement("ALTER TABLE prenatal_checkups MODIFY COLUMN status ENUM('upcoming', 'done') NOT NULL DEFAULT 'upcoming'");
    }
};
