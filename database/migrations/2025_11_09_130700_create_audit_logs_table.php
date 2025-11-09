<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit Logs Migration
 *
 * Creates a comprehensive audit logging table for tracking sensitive
 * operations and security-related events across the application.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // User information
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name')->nullable(); // Store name in case user is deleted
            $table->string('user_role')->nullable();

            // Event details
            $table->string('event')->index(); // e.g., 'user.login', 'patient.created', 'vaccine.deleted'
            $table->string('auditable_type')->nullable()->index(); // Model class name
            $table->unsignedBigInteger('auditable_id')->nullable()->index(); // Model ID
            $table->string('action'); // create, update, delete, login, logout, etc.

            // Request details
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, DELETE

            // Data tracking
            $table->json('old_values')->nullable(); // Before state
            $table->json('new_values')->nullable(); // After state
            $table->json('metadata')->nullable(); // Additional context

            // Security tracking
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low')->index();
            $table->string('tags')->nullable(); // Comma-separated tags for filtering

            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['user_id', 'created_at']);
            $table->index(['event', 'created_at']);
            $table->index(['severity', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
