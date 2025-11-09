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
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->select(
            "SELECT COUNT(*) as count
             FROM information_schema.statistics
             WHERE table_schema = ?
             AND table_name = ?
             AND index_name = ?",
            [$database, $table, $indexName]
        );

        return $result[0]->count > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ===================================================================
        // CHILD_IMMUNIZATIONS TABLE
        // ===================================================================
        Schema::table('child_immunizations', function (Blueprint $table) {
            // Skip child_record_id index - automatically created by foreignId()->constrained()

            // Date index for date range queries and sorting
            if (!$this->indexExists('child_immunizations', 'idx_child_immunizations_vaccination_date')) {
                $table->index('vaccination_date', 'idx_child_immunizations_vaccination_date');
            }

            // Vaccine name index for filtering by vaccine type
            if (!$this->indexExists('child_immunizations', 'idx_child_immunizations_vaccine_name')) {
                $table->index('vaccine_name', 'idx_child_immunizations_vaccine_name');
            }

            // Composite index for common query: filter by child and sort by date
            if (!$this->indexExists('child_immunizations', 'idx_child_immunizations_child_date')) {
                $table->index(['child_record_id', 'vaccination_date'], 'idx_child_immunizations_child_date');
            }
        });

        // ===================================================================
        // PRENATAL_CHECKUPS TABLE (CRITICAL - Missing patient_id index!)
        // ===================================================================
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            // CRITICAL: Foreign key index for patient lookups (currently missing!)
            if (!$this->indexExists('prenatal_checkups', 'idx_prenatal_checkups_patient')) {
                $table->index('patient_id', 'idx_prenatal_checkups_patient');
            }

            // Status index for filtering (upcoming, completed, cancelled)
            if (!$this->indexExists('prenatal_checkups', 'idx_prenatal_checkups_status')) {
                $table->index('status', 'idx_prenatal_checkups_status');
            }

            // Checkup date index for date range queries and calendar views
            if (!$this->indexExists('prenatal_checkups', 'idx_prenatal_checkups_checkup_date')) {
                $table->index('checkup_date', 'idx_prenatal_checkups_checkup_date');
            }

            // Composite index for common query: filter by status and sort by date
            if (!$this->indexExists('prenatal_checkups', 'idx_prenatal_checkups_status_date')) {
                $table->index(['status', 'checkup_date'], 'idx_prenatal_checkups_status_date');
            }

            // Composite index for patient's checkup history
            if (!$this->indexExists('prenatal_checkups', 'idx_prenatal_checkups_patient_date')) {
                $table->index(['patient_id', 'checkup_date'], 'idx_prenatal_checkups_patient_date');
            }
        });

        // ===================================================================
        // USERS TABLE
        // ===================================================================
        Schema::table('users', function (Blueprint $table) {
            // Role index for role-based queries (midwife, bhw, admin)
            if (!$this->indexExists('users', 'idx_users_role')) {
                $table->index('role', 'idx_users_role');
            }

            // Active status index for filtering active/inactive users
            if (!$this->indexExists('users', 'idx_users_is_active')) {
                $table->index('is_active', 'idx_users_is_active');
            }

            // Composite index for common query: active users by role
            if (!$this->indexExists('users', 'idx_users_role_active')) {
                $table->index(['role', 'is_active'], 'idx_users_role_active');
            }

            // Created at index for recent users queries
            if (!$this->indexExists('users', 'idx_users_created_at')) {
                $table->index('created_at', 'idx_users_created_at');
            }
        });

        // ===================================================================
        // PATIENTS TABLE
        // ===================================================================
        Schema::table('patients', function (Blueprint $table) {
            // Age index for demographic filtering and reports
            if (!$this->indexExists('patients', 'idx_patients_age')) {
                $table->index('age', 'idx_patients_age');
            }

            // Created at index for new patient queries and reports
            if (!$this->indexExists('patients', 'idx_patients_created_at')) {
                $table->index('created_at', 'idx_patients_created_at');
            }

            // Soft deletes index for filtering active patients
            if (!$this->indexExists('patients', 'idx_patients_deleted_at')) {
                $table->index('deleted_at', 'idx_patients_deleted_at');
            }
        });

        // ===================================================================
        // VACCINES TABLE
        // ===================================================================
        Schema::table('vaccines', function (Blueprint $table) {
            // Current stock index for inventory queries
            if (!$this->indexExists('vaccines', 'idx_vaccines_current_stock')) {
                $table->index('current_stock', 'idx_vaccines_current_stock');
            }

            // Composite index for low stock alerts (stock <= min_stock)
            if (!$this->indexExists('vaccines', 'idx_vaccines_stock_levels')) {
                $table->index(['current_stock', 'min_stock'], 'idx_vaccines_stock_levels');
            }
        });

        // ===================================================================
        // NOTIFICATIONS TABLE
        // ===================================================================
        Schema::table('notifications', function (Blueprint $table) {
            // Read status index for unread notifications queries
            if (!$this->indexExists('notifications', 'idx_notifications_read_at')) {
                $table->index('read_at', 'idx_notifications_read_at');
            }

            // Skip created_at index - already exists from 2025_09_12_004646_add_indexes_to_notifications_table
            // Skip composite index - similar index already exists as 'idx_notifications_user_read'
        });

        // ===================================================================
        // PRENATAL_RECORDS TABLE
        // ===================================================================
        Schema::table('prenatal_records', function (Blueprint $table) {
            // Created at index for recent records queries
            if (!$this->indexExists('prenatal_records', 'idx_prenatal_records_created_at')) {
                $table->index('created_at', 'idx_prenatal_records_created_at');
            }

            // Soft deletes index for filtering active records
            if (!$this->indexExists('prenatal_records', 'idx_prenatal_records_deleted_at')) {
                $table->index('deleted_at', 'idx_prenatal_records_deleted_at');
            }

            // Composite index for patient's active records
            if (!$this->indexExists('prenatal_records', 'idx_prenatal_records_patient_active')) {
                $table->index(['patient_id', 'deleted_at'], 'idx_prenatal_records_patient_active');
            }
        });

        // ===================================================================
        // IMMUNIZATIONS TABLE - Additional Composite Index
        // ===================================================================
        Schema::table('immunizations', function (Blueprint $table) {
            // Composite index for vaccine scheduling queries
            if (!$this->indexExists('immunizations', 'idx_immunizations_vaccine_status_date')) {
                $table->index(['vaccine_id', 'status', 'schedule_date'], 'idx_immunizations_vaccine_status_date');
            }
        });

        // ===================================================================
        // PRENATAL_VISITS TABLE
        // ===================================================================
        if (Schema::hasTable('prenatal_visits')) {
            Schema::table('prenatal_visits', function (Blueprint $table) {
                // Index on next_visit_date for upcoming visits queries
                if (!$this->indexExists('prenatal_visits', 'idx_prenatal_visits_next_visit_date')) {
                    $table->index('next_visit_date', 'idx_prenatal_visits_next_visit_date');
                }
            });
        }

        // ===================================================================
        // APPOINTMENTS TABLE - Additional Index
        // ===================================================================
        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                // Index on conducted_by for healthcare worker filtering
                if (!$this->indexExists('appointments', 'idx_appointments_conducted_by')) {
                    $table->index('conducted_by', 'idx_appointments_conducted_by');
                }

                // Soft deletes index
                if (!$this->indexExists('appointments', 'idx_appointments_deleted_at')) {
                    $table->index('deleted_at', 'idx_appointments_deleted_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes in reverse order

        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                if ($this->indexExists('appointments', 'idx_appointments_deleted_at')) {
                    $table->dropIndex('idx_appointments_deleted_at');
                }
                if ($this->indexExists('appointments', 'idx_appointments_conducted_by')) {
                    $table->dropIndex('idx_appointments_conducted_by');
                }
            });
        }

        if (Schema::hasTable('prenatal_visits')) {
            Schema::table('prenatal_visits', function (Blueprint $table) {
                if ($this->indexExists('prenatal_visits', 'idx_prenatal_visits_next_visit_date')) {
                    $table->dropIndex('idx_prenatal_visits_next_visit_date');
                }
            });
        }

        Schema::table('immunizations', function (Blueprint $table) {
            if ($this->indexExists('immunizations', 'idx_immunizations_vaccine_status_date')) {
                $table->dropIndex('idx_immunizations_vaccine_status_date');
            }
        });

        Schema::table('prenatal_records', function (Blueprint $table) {
            if ($this->indexExists('prenatal_records', 'idx_prenatal_records_patient_active')) {
                $table->dropIndex('idx_prenatal_records_patient_active');
            }
            if ($this->indexExists('prenatal_records', 'idx_prenatal_records_deleted_at')) {
                $table->dropIndex('idx_prenatal_records_deleted_at');
            }
            if ($this->indexExists('prenatal_records', 'idx_prenatal_records_created_at')) {
                $table->dropIndex('idx_prenatal_records_created_at');
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if ($this->indexExists('notifications', 'idx_notifications_read_at')) {
                $table->dropIndex('idx_notifications_read_at');
            }
        });

        Schema::table('vaccines', function (Blueprint $table) {
            if ($this->indexExists('vaccines', 'idx_vaccines_stock_levels')) {
                $table->dropIndex('idx_vaccines_stock_levels');
            }
            if ($this->indexExists('vaccines', 'idx_vaccines_current_stock')) {
                $table->dropIndex('idx_vaccines_current_stock');
            }
        });

        Schema::table('patients', function (Blueprint $table) {
            if ($this->indexExists('patients', 'idx_patients_deleted_at')) {
                $table->dropIndex('idx_patients_deleted_at');
            }
            if ($this->indexExists('patients', 'idx_patients_created_at')) {
                $table->dropIndex('idx_patients_created_at');
            }
            if ($this->indexExists('patients', 'idx_patients_age')) {
                $table->dropIndex('idx_patients_age');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if ($this->indexExists('users', 'idx_users_created_at')) {
                $table->dropIndex('idx_users_created_at');
            }
            if ($this->indexExists('users', 'idx_users_role_active')) {
                $table->dropIndex('idx_users_role_active');
            }
            if ($this->indexExists('users', 'idx_users_is_active')) {
                $table->dropIndex('idx_users_is_active');
            }
            if ($this->indexExists('users', 'idx_users_role')) {
                $table->dropIndex('idx_users_role');
            }
        });

        Schema::table('prenatal_checkups', function (Blueprint $table) {
            if ($this->indexExists('prenatal_checkups', 'idx_prenatal_checkups_patient_date')) {
                $table->dropIndex('idx_prenatal_checkups_patient_date');
            }
            if ($this->indexExists('prenatal_checkups', 'idx_prenatal_checkups_status_date')) {
                $table->dropIndex('idx_prenatal_checkups_status_date');
            }
            if ($this->indexExists('prenatal_checkups', 'idx_prenatal_checkups_checkup_date')) {
                $table->dropIndex('idx_prenatal_checkups_checkup_date');
            }
            if ($this->indexExists('prenatal_checkups', 'idx_prenatal_checkups_status')) {
                $table->dropIndex('idx_prenatal_checkups_status');
            }
            if ($this->indexExists('prenatal_checkups', 'idx_prenatal_checkups_patient')) {
                $table->dropIndex('idx_prenatal_checkups_patient');
            }
        });

        Schema::table('child_immunizations', function (Blueprint $table) {
            if ($this->indexExists('child_immunizations', 'idx_child_immunizations_child_date')) {
                $table->dropIndex('idx_child_immunizations_child_date');
            }
            if ($this->indexExists('child_immunizations', 'idx_child_immunizations_vaccine_name')) {
                $table->dropIndex('idx_child_immunizations_vaccine_name');
            }
            if ($this->indexExists('child_immunizations', 'idx_child_immunizations_vaccination_date')) {
                $table->dropIndex('idx_child_immunizations_vaccination_date');
            }
            // Skip child_record_id index - it's a foreign key index, not created by this migration
        });
    }
};
