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
        Schema::create('immunizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('child_record_id');
            $table->string('vaccine_name');
            $table->string('dose');
            $table->date('schedule_date');
            $table->time('schedule_time');
            $table->enum('status', ['Upcoming', 'Done', 'Missed'])->default('Upcoming'); 
            $table->text('notes')->nullable();
            $table->date('next_due_date')->nullable();
            $table->timestamps();
            
            // Add foreign key constraint
            $table->foreign('child_record_id')
                  ->references('id')
                  ->on('child_records')
                  ->onDelete('cascade');
            
            // Add indexes for better query performance
            $table->index('status');
            $table->index('schedule_date');
            $table->index(['child_record_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('immunizations');
    }
};