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
        Schema::create('child_immunizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_record_id')->constrained('child_records')->onDelete('cascade');
            $table->string('vaccine_name');
            $table->text('vaccine_description')->nullable();
            $table->date('vaccination_date');
            $table->string('administered_by');
            $table->string('batch_number')->nullable();
            $table->text('notes')->nullable();
            $table->string('next_due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_immunizations');
    }
};
