<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('formatted_patient_id')->unique();
            $table->string('name');
            $table->integer('age');
            $table->string('contact')->nullable();
            $table->string('emergency_contact')->nullable(); // Added for emergency contact
            $table->text('address')->nullable();
            $table->string('occupation')->nullable(); // Added for occupation
            $table->timestamps();
            $table->softDeletes();
        
            $table->index(['name']);
            $table->index(['formatted_patient_id']);
        });

        Schema::create('prenatal_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->date('last_menstrual_period');
            $table->date('expected_due_date')->nullable();
            $table->string('gestational_age')->nullable();
            $table->integer('trimester')->nullable();
            $table->integer('gravida')->nullable();
            $table->integer('para')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('notes')->nullable();
            $table->date('last_visit')->nullable();
            $table->dateTime('next_appointment')->nullable();
            $table->enum('status', ['normal', 'monitor', 'high-risk', 'due', 'completed'])->default('normal'); // Added 'completed' status
            
            // Added physical measurements fields
            $table->string('blood_pressure')->nullable(); // Store as string like "120/80"
            $table->decimal('weight', 5, 2)->nullable(); // Weight in kg
            $table->integer('height')->nullable(); // Height in cm
            
            $table->timestamps();
            $table->softDeletes();
        
            $table->index(['status']);
            $table->index(['last_menstrual_period']);
            $table->index(['expected_due_date']);
            $table->index(['patient_id']); // Added index for patient lookup
        });
        
        Schema::create('prenatal_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prenatal_record_id')->constrained('prenatal_records')->onDelete('cascade');
            $table->dateTime('appointment_date');
            $table->enum('appointment_type', ['routine', 'follow-up', 'ultrasound', 'lab', 'emergency'])->default('routine'); // Added 'emergency' type
            $table->text('notes')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no-show', 'rescheduled'])->default('scheduled'); // Added 'rescheduled' status
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

    public function down()
    {
        Schema::dropIfExists('prenatal_visits');
        Schema::dropIfExists('prenatal_appointments');
        Schema::dropIfExists('prenatal_records');
        Schema::dropIfExists('patients'); // Added this line to properly drop patients table
    }
};