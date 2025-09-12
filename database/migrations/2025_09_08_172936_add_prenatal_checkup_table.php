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
        Schema::create('prenatal_checkups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->date('checkup_date');
            $table->time('checkup_time');
            $table->string('weeks_pregnant')->nullable();
            
            // Vital Signs
            $table->integer('bp_high')->nullable(); // Blood pressure high
            $table->integer('bp_low')->nullable(); // Blood pressure low
            $table->decimal('weight', 5, 2)->nullable();
            $table->integer('baby_heartbeat')->nullable();
            $table->decimal('belly_size', 5, 2)->nullable();
            
            // Health Check
            $table->enum('baby_movement', ['active', 'normal', 'less'])->nullable();
            $table->json('swelling')->nullable(); // Store array of swelling locations
            
            // Notes and Follow-up
            $table->text('notes')->nullable();
            $table->date('next_visit_date')->nullable();
            $table->time('next_visit_time')->nullable();
            $table->text('next_visit_notes')->nullable();
            
            $table->enum('status', ['completed', 'upcoming', 'cancelled'])->default('upcoming');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prenatal_checkups');
    }
};