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
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            // Add appointment relationship if it doesn't exist
            if (!Schema::hasColumn('prenatal_checkups', 'appointment_id')) {
                $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('cascade')->after('prenatal_record_id');
            }

            // Remove scheduling-related fields (keep as backup first)
            // We'll drop these in a separate migration after data transfer

            // Clean up duplicate/redundant fields - standardize on newer format
            // Keep: gestational_age_weeks (drop weeks_pregnant)
            // Keep: weight_kg (drop weight)
            // Keep: blood_pressure_systolic/diastolic (drop bp_high/bp_low)
            // Keep: fetal_heart_rate (drop baby_heartbeat)
            // Keep: fundal_height_cm (drop belly_size)

            // Remove next visit fields as they'll be in appointments
            if (Schema::hasColumn('prenatal_checkups', 'next_visit_date')) {
                // Will drop these after data migration
            }

            // Status enum will be updated in a separate migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropColumn('appointment_id');

            // Status enum restoration handled in separate migration
        });
    }
};
