<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Performance Indexes to Database Tables
 *
 * This migration adds missing database indexes to improve query performance
 * across the application. Indexes are added for:
 * - Foreign keys that are frequently joined
 * - Columns used in WHERE clauses and filtering
 * - Columns used in ORDER BY operations
 * - Composite indexes for common query patterns
 *
 * Performance Impact:
 * - Improves JOIN performance on foreign keys
 * - Speeds up filtered queries (status, dates, etc.)
 * - Optimizes pagination and sorting operations
 * - Enhances dashboard and report generation speed
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ===================================================================
        // CHILD_IMMUNIZATIONS TABLE
        // ===================================================================
        Schema::table('child_immunizations', function (Blueprint $table) {
            // Foreign key index for child lookups (JOIN optimization)
            $table->index('child_record_id', 'idx_child_immunizations_child_record');

            // Date index for date range queries and sorting
            $table->index('vaccination_date', 'idx_child_immunizations_vaccination_date');

            // Vaccine name index for filtering by vaccine type
            $table->index('vaccine_name', 'idx_child_immunizations_vaccine_name');

            // Composite index for common query: filter by child and sort by date
            $table->index(['child_record_id', 'vaccination_date'], 'idx_child_immunizations_child_date');
        });

        // ===================================================================
        // PRENATAL_CHECKUPS TABLE (CRITICAL - Missing patient_id index!)
        // ===================================================================
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            // CRITICAL: Foreign key index for patient lookups (currently missing!)
            $table->index('patient_id', 'idx_prenatal_checkups_patient');

            // Status index for filtering (upcoming, completed, cancelled)
            $table->index('status', 'idx_prenatal_checkups_status');

            // Checkup date index for date range queries and calendar views
            $table->index('checkup_date', 'idx_prenatal_checkups_checkup_date');

            // Composite index for common query: filter by status and sort by date
            $table->index(['status', 'checkup_date'], 'idx_prenatal_checkups_status_date');

            // Composite index for patient's checkup history
            $table->index(['patient_id', 'checkup_date'], 'idx_prenatal_checkups_patient_date');
        });

        // ===================================================================
        // USERS TABLE
        // ===================================================================
        Schema::table('users', function (Blueprint $table) {
            // Role index for role-based queries (midwife, bhw, admin)
            $table->index('role', 'idx_users_role');

            // Active status index for filtering active/inactive users
            $table->index('is_active', 'idx_users_is_active');

            // Composite index for common query: active users by role
            $table->index(['role', 'is_active'], 'idx_users_role_active');

            // Created at index for recent users queries
            $table->index('created_at', 'idx_users_created_at');
        });

        // ===================================================================
        // PATIENTS TABLE
        // ===================================================================
        Schema::table('patients', function (Blueprint $table) {
            // Age index for demographic filtering and reports
            $table->index('age', 'idx_patients_age');

            // Created at index for new patient queries and reports
            $table->index('created_at', 'idx_patients_created_at');

            // Soft deletes index for filtering active patients
            $table->index('deleted_at', 'idx_patients_deleted_at');
        });

        // ===================================================================
        // VACCINES TABLE
        // ===================================================================
        Schema::table('vaccines', function (Blueprint $table) {
            // Current stock index for inventory queries
            $table->index('current_stock', 'idx_vaccines_current_stock');

            // Composite index for low stock alerts (stock <= min_stock)
            $table->index(['current_stock', 'min_stock'], 'idx_vaccines_stock_levels');
        });

        // ===================================================================
        // NOTIFICATIONS TABLE
        // ===================================================================
        Schema::table('notifications', function (Blueprint $table) {
            // Read status index for unread notifications queries
            $table->index('read_at', 'idx_notifications_read_at');

            // Created at index for recent notifications
            $table->index('created_at', 'idx_notifications_created_at');

            // Composite index for user's unread notifications
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'idx_notifications_user_unread');
        });

        // ===================================================================
        // PRENATAL_RECORDS TABLE
        // ===================================================================
        Schema::table('prenatal_records', function (Blueprint $table) {
            // Created at index for recent records queries
            $table->index('created_at', 'idx_prenatal_records_created_at');

            // Soft deletes index for filtering active records
            $table->index('deleted_at', 'idx_prenatal_records_deleted_at');

            // Composite index for patient's active records
            $table->index(['patient_id', 'deleted_at'], 'idx_prenatal_records_patient_active');
        });

        // ===================================================================
        // IMMUNIZATIONS TABLE - Additional Composite Index
        // ===================================================================
        Schema::table('immunizations', function (Blueprint $table) {
            // Composite index for vaccine scheduling queries
            $table->index(['vaccine_id', 'status', 'schedule_date'], 'idx_immunizations_vaccine_status_date');
        });

        // ===================================================================
        // PRENATAL_VISITS TABLE
        // ===================================================================
        Schema::table('prenatal_visits', function (Blueprint $table) {
            // Index on next_visit_date for upcoming visits queries
            $table->index('next_visit_date', 'idx_prenatal_visits_next_visit_date');
        });

        // ===================================================================
        // APPOINTMENTS TABLE - Additional Index
        // ===================================================================
        Schema::table('appointments', function (Blueprint $table) {
            // Index on conducted_by for healthcare worker filtering
            $table->index('conducted_by', 'idx_appointments_conducted_by');

            // Soft deletes index
            $table->index('deleted_at', 'idx_appointments_deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes in reverse order

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('idx_appointments_deleted_at');
            $table->dropIndex('idx_appointments_conducted_by');
        });

        Schema::table('prenatal_visits', function (Blueprint $table) {
            $table->dropIndex('idx_prenatal_visits_next_visit_date');
        });

        Schema::table('immunizations', function (Blueprint $table) {
            $table->dropIndex('idx_immunizations_vaccine_status_date');
        });

        Schema::table('prenatal_records', function (Blueprint $table) {
            $table->dropIndex('idx_prenatal_records_patient_active');
            $table->dropIndex('idx_prenatal_records_deleted_at');
            $table->dropIndex('idx_prenatal_records_created_at');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_unread');
            $table->dropIndex('idx_notifications_created_at');
            $table->dropIndex('idx_notifications_read_at');
        });

        Schema::table('vaccines', function (Blueprint $table) {
            $table->dropIndex('idx_vaccines_stock_levels');
            $table->dropIndex('idx_vaccines_current_stock');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('idx_patients_deleted_at');
            $table->dropIndex('idx_patients_created_at');
            $table->dropIndex('idx_patients_age');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_created_at');
            $table->dropIndex('idx_users_role_active');
            $table->dropIndex('idx_users_is_active');
            $table->dropIndex('idx_users_role');
        });

        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->dropIndex('idx_prenatal_checkups_patient_date');
            $table->dropIndex('idx_prenatal_checkups_status_date');
            $table->dropIndex('idx_prenatal_checkups_checkup_date');
            $table->dropIndex('idx_prenatal_checkups_status');
            $table->dropIndex('idx_prenatal_checkups_patient');
        });

        Schema::table('child_immunizations', function (Blueprint $table) {
            $table->dropIndex('idx_child_immunizations_child_date');
            $table->dropIndex('idx_child_immunizations_vaccine_name');
            $table->dropIndex('idx_child_immunizations_vaccination_date');
            $table->dropIndex('idx_child_immunizations_child_record');
        });
    }
};
