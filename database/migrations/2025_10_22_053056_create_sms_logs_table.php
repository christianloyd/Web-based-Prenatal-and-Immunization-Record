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
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_number'); // Phone number
            $table->string('recipient_name')->nullable(); // Patient/Child name
            $table->text('message'); // SMS content
            $table->string('type'); // Type: appointment_reminder, vaccination_reminder, missed_appointment, etc.
            $table->string('status')->default('sent'); // sent, failed
            $table->text('response')->nullable(); // API response
            $table->string('related_type')->nullable(); // Model type: Immunization, PrenatalCheckup, etc.
            $table->unsignedBigInteger('related_id')->nullable(); // Model ID
            $table->unsignedBigInteger('sent_by')->nullable(); // User who triggered the SMS
            $table->foreign('sent_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for better query performance
            $table->index('recipient_number');
            $table->index('type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
