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
        // Drop unused prenatal-related tables
        // Keep only 'patients' and 'prenatal_records' tables
        
         
        Schema::dropIfExists('prenatal_visits'); 
        Schema::dropIfExists('prenatal_appointments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the dropped tables if needed for rollback
        
        Schema::create('prenatal_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prenatal_record_id')->constrained('prenatal_records')->onDelete('cascade');
            $table->dateTime('appointment_date');
            $table->enum('appointment_type', ['routine', 'follow-up', 'ultrasound', 'lab', 'emergency'])->default('routine');
            $table->text('notes')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no-show', 'rescheduled'])->default('scheduled');
            $table->timestamps();
            
            $table->index(['prenatal_record_id']);
            $table->index(['appointment_date']);
            $table->index(['status']);
        });

        Schema::create('prenatal_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prenatal_record_id')->constrained('prenatal_records')->onDelete('cascade');
            $table->date('visit_date');
            $table->string('gestational_age')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->integer('blood_pressure_systolic')->nullable();
            $table->integer('blood_pressure_diastolic')->nullable();
            $table->integer('fetal_heart_rate')->nullable();
            $table->decimal('fundal_height', 4, 1)->nullable();
            $table->text('complaints')->nullable();
            $table->text('physical_exam')->nullable();
            $table->text('lab_results')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('notes')->nullable();
            $table->date('next_visit_date')->nullable();
            $table->timestamps();
            
            $table->index(['prenatal_record_id', 'visit_date']);
            $table->index(['visit_date']);
        });

        
    }
};