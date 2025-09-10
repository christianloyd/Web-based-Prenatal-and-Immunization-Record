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
        Schema::create('child_records', function (Blueprint $table) {
            $table->id();
            $table->string('child_name');
            $table->enum('gender', ['Male', 'Female']);
            $table->decimal('birth_height', 6, 2)->nullable(); // Changed to 6,2 for values like 999.99 and made nullable
            $table->decimal('birth_weight', 6, 3)->nullable(); // Changed to 6,3 for values like 99.999 and made nullable
            $table->date('birthdate');
            $table->string('birthplace')->nullable(); // Made nullable
            $table->text('address')->nullable(); // Changed to text and made nullable
            $table->string('father_name')->nullable(); // Made nullable
            $table->string('phone_number', 20);
            $table->string('mother_name')->nullable(); // Added this field for storing mother name
            
            $table->foreignId('mother_id')->nullable()->constrained('patients')->onDelete('set null');
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['child_name']);
            $table->index(['birthdate']);
            $table->index(['mother_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_records');
    }
};