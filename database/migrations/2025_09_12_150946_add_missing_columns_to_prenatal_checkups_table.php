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
            // Add soft deletes support if missing
            if (!Schema::hasColumn('prenatal_checkups', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
            
            // Add only columns that are likely missing (avoiding duplicates)
            if (!Schema::hasColumn('prenatal_checkups', 'formatted_checkup_id')) {
                $table->string('formatted_checkup_id')->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('prenatal_checkups', 'prenatal_record_id')) {
                $table->foreignId('prenatal_record_id')->nullable()->constrained('prenatal_records')->onDelete('cascade')->after('patient_id');
            }
            if (!Schema::hasColumn('prenatal_checkups', 'gestational_age_weeks')) {
                $table->integer('gestational_age_weeks')->nullable()->after('weeks_pregnant');
            }
            if (!Schema::hasColumn('prenatal_checkups', 'weight_kg')) {
                $table->decimal('weight_kg', 5, 2)->nullable()->after('weight');
            }
            if (!Schema::hasColumn('prenatal_checkups', 'blood_pressure_systolic')) {
                $table->integer('blood_pressure_systolic')->nullable()->after('bp_low');
            }
            if (!Schema::hasColumn('prenatal_checkups', 'blood_pressure_diastolic')) {
                $table->integer('blood_pressure_diastolic')->nullable()->after('blood_pressure_systolic');
            }
            if (!Schema::hasColumn('prenatal_checkups', 'fetal_heart_rate')) {
                $table->integer('fetal_heart_rate')->nullable()->after('baby_heartbeat');
            }
            if (!Schema::hasColumn('prenatal_checkups', 'fundal_height_cm')) {
                $table->decimal('fundal_height_cm', 4, 1)->nullable()->after('belly_size');
            }
            if (!Schema::hasColumn('prenatal_checkups', 'presentation')) {
                $table->string('presentation')->nullable()->after('fundal_height_cm');
            }
            if (!Schema::hasColumn('prenatal_checkups', 'symptoms')) {
                $table->text('symptoms')->nullable()->after('presentation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            // Remove added columns in reverse order
            $table->dropForeign(['conducted_by']);
            $table->dropColumn('conducted_by');
            $table->dropColumn('symptoms');
            $table->dropColumn('presentation');
            $table->dropColumn('fundal_height_cm');
            $table->dropColumn('fetal_heart_rate');
            $table->dropColumn('blood_pressure_diastolic');
            $table->dropColumn('blood_pressure_systolic');
            $table->dropColumn('weight_kg');
            $table->dropColumn('gestational_age_weeks');
            $table->dropForeign(['prenatal_record_id']);
            $table->dropColumn('prenatal_record_id');
            $table->dropColumn('formatted_checkup_id');
            $table->dropSoftDeletes();
        });
    }
};
