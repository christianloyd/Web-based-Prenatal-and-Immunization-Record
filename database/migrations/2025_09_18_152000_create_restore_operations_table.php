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
        Schema::create('restore_operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('backup_id'); // Reference to the backup used for restore
            $table->string('backup_name'); // Name of the backup at time of restore (in case backup gets deleted)
            $table->json('modules_restored'); // Modules that were restored
            $table->enum('status', ['completed', 'failed'])->default('completed');
            $table->json('restore_options')->nullable(); // Options used during restore
            $table->text('error_message')->nullable(); // Error details if restore fails
            $table->timestamp('restored_at'); // When the restore was performed
            $table->unsignedBigInteger('restored_by'); // User who performed the restore
            $table->timestamps();

            $table->foreign('backup_id')->references('id')->on('cloud_backups')->onDelete('cascade');
            $table->foreign('restored_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status', 'restored_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restore_operations');
    }
};