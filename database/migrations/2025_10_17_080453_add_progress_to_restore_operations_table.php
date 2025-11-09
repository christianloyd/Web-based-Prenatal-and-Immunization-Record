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
        Schema::table('restore_operations', function (Blueprint $table) {
            // Add progress tracking fields
            $table->integer('progress')->default(0)->after('status');
            $table->string('current_step')->nullable()->after('progress');
            $table->timestamp('started_at')->nullable()->after('current_step');
            $table->timestamp('completed_at')->nullable()->after('started_at');

            // Modify status enum to include 'in_progress' and 'pending'
            $table->dropColumn('status');
        });

        Schema::table('restore_operations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending')->after('modules_restored');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restore_operations', function (Blueprint $table) {
            $table->dropColumn(['progress', 'current_step', 'started_at', 'completed_at']);
            $table->dropColumn('status');
        });

        Schema::table('restore_operations', function (Blueprint $table) {
            $table->enum('status', ['completed', 'failed'])->default('completed')->after('modules_restored');
        });
    }
};
