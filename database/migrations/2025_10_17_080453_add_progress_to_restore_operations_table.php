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

        Schema::table('restore_operations', function (Blueprint $table) use ($driver) {
            if (!Schema::hasColumn('restore_operations', 'progress')) {
                $column = $table->integer('progress')->default(0);
                if ($driver === 'mysql') {
                    $column->after('status');
                }
            }

            if (!Schema::hasColumn('restore_operations', 'current_step')) {
                $column = $table->string('current_step')->nullable();
                if ($driver === 'mysql') {
                    $column->after('progress');
                }
            }

            if (!Schema::hasColumn('restore_operations', 'started_at')) {
                $column = $table->timestamp('started_at')->nullable();
                if ($driver === 'mysql') {
                    $column->after('current_step');
                }
            }

            if (!Schema::hasColumn('restore_operations', 'completed_at')) {
                $column = $table->timestamp('completed_at')->nullable();
                if ($driver === 'mysql') {
                    $column->after('started_at');
                }
            }
        });

        if ($driver === 'mysql' && Schema::hasColumn('restore_operations', 'status')) {
            Schema::table('restore_operations', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('restore_operations', function (Blueprint $table) {
                $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])
                    ->default('pending')
                    ->after('modules_restored');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        $columnsToDrop = collect(['progress', 'current_step', 'started_at', 'completed_at'])
            ->filter(fn ($column) => Schema::hasColumn('restore_operations', $column))
            ->values()
            ->all();

        if (!empty($columnsToDrop)) {
            Schema::table('restore_operations', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }

        if ($driver === 'mysql' && Schema::hasColumn('restore_operations', 'status')) {
            Schema::table('restore_operations', function (Blueprint $table) {
                $table->dropColumn('status');
            });

            Schema::table('restore_operations', function (Blueprint $table) {
                $table->enum('status', ['completed', 'failed'])
                    ->default('completed')
                    ->after('modules_restored');
            });
        }
    }
};
