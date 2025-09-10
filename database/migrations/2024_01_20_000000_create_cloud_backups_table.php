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
        Schema::create('cloud_backups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['full', 'selective']);
            $table->string('format')->default('sql_dump');
            $table->json('modules'); // Array of selected modules to backup
            $table->string('file_path')->nullable(); // Path to the backup file
            $table->string('file_size')->nullable(); // Size of the backup file
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->string('storage_location')->default('google_drive');
            $table->boolean('encrypted')->default(true);
            $table->boolean('compressed')->default(true);
            $table->boolean('verified')->default(false);
            $table->string('google_drive_file_id')->nullable(); // Google Drive file ID
            $table->text('google_drive_link')->nullable(); // Google Drive web view link
            $table->text('error_message')->nullable(); // Store error details if backup fails
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by'); // User who created the backup
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_backups');
    }
};