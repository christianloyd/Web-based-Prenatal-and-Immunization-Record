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
        // Add composite indexes for patients table
        Schema::table('patients', function (Blueprint $table) {
            $table->index(['first_name', 'last_name'], 'idx_patients_name');
            $table->index(['created_at', 'age'], 'idx_patients_created_age');
            $table->index(['contact'], 'idx_patients_contact');
        });

        // Add composite indexes for prenatal_checkups table
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->index(['checkup_date', 'status'], 'idx_checkups_date_status');
            $table->index(['patient_id', 'status'], 'idx_checkups_patient_status');
            $table->index(['status', 'checkup_date'], 'idx_checkups_status_date');
            $table->index(['checkup_date'], 'idx_checkups_date');
        });

        // Add indexes for immunizations table
        Schema::table('immunizations', function (Blueprint $table) {
            $table->index(['child_record_id', 'status'], 'idx_immunizations_child_status');
            $table->index(['schedule_date', 'status'], 'idx_immunizations_schedule_status');
            $table->index(['vaccine_name'], 'idx_immunizations_vaccine_name');
        });

        // Add indexes for child_records table
        Schema::table('child_records', function (Blueprint $table) {
            $table->index(['gender'], 'idx_child_records_gender');
            $table->index(['birthdate'], 'idx_child_records_birthdate');
            $table->index(['created_at'], 'idx_child_records_created');
        });

        // Add indexes for prenatal_records table
        Schema::table('prenatal_records', function (Blueprint $table) {
            $table->index(['is_active', 'status'], 'idx_prenatal_records_active_status');
            $table->index(['patient_id', 'is_active'], 'idx_prenatal_records_patient_active');
        });

        // Add indexes for notifications table (if it exists)
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['notifiable_id', 'read_at'], 'idx_notifications_notifiable_read');
                $table->index(['created_at'], 'idx_notifications_created');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes for patients table
        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('idx_patients_name');
            $table->dropIndex('idx_patients_created_age');
            $table->dropIndex('idx_patients_contact');
        });

        // Drop indexes for prenatal_checkups table
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->dropIndex('idx_checkups_date_status');
            $table->dropIndex('idx_checkups_patient_status');
            $table->dropIndex('idx_checkups_status_date');
            $table->dropIndex('idx_checkups_date');
        });

        // Drop indexes for immunizations table
        Schema::table('immunizations', function (Blueprint $table) {
            $table->dropIndex('idx_immunizations_child_status');
            $table->dropIndex('idx_immunizations_schedule_status');
            $table->dropIndex('idx_immunizations_vaccine_name');
        });

        // Drop indexes for child_records table
        Schema::table('child_records', function (Blueprint $table) {
            $table->dropIndex('idx_child_records_gender');
            $table->dropIndex('idx_child_records_birthdate');
            $table->dropIndex('idx_child_records_created');
        });

        // Drop indexes for prenatal_records table
        Schema::table('prenatal_records', function (Blueprint $table) {
            $table->dropIndex('idx_prenatal_records_active_status');
            $table->dropIndex('idx_prenatal_records_patient_active');
        });

        // Drop indexes for notifications table (if it exists)
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropIndex('idx_notifications_notifiable_read');
                $table->dropIndex('idx_notifications_created');
            });
        }
    }
};
