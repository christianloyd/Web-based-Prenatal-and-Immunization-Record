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
        Schema::create('vaccines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('dosage');
            $table->integer('current_stock')->default(0);
            $table->integer('min_stock')->default(10);
            $table->date('expiry_date');
            $table->string('storage_temp');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Add indexes
            $table->index('name');
            $table->index('category');
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccines');
    }
};