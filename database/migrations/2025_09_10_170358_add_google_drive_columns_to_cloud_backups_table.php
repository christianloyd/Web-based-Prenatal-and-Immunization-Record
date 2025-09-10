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
        Schema::table('cloud_backups', function (Blueprint $table) {
            // Add Google Drive columns if they don't exist
            if (!Schema::hasColumn('cloud_backups', 'google_drive_file_id')) {
                $table->string('google_drive_file_id')->nullable();
            }
            if (!Schema::hasColumn('cloud_backups', 'google_drive_link')) {
                $table->text('google_drive_link')->nullable();
            }
            if (!Schema::hasColumn('cloud_backups', 'storage_location')) {
                $table->string('storage_location')->default('local');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cloud_backups', function (Blueprint $table) {
            $table->dropColumn(['google_drive_file_id', 'google_drive_link', 'storage_location']);
        });
    }
};
