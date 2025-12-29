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
        Schema::table('vaccines', function (Blueprint $table) {
            // JSON field to store age-based schedule for each vaccine
            // Structure: {"doses": [{"dose_number": 1, "age": 6, "unit": "weeks", "label": "1st Dose"}]}
            $table->json('age_schedule')->nullable()->after('dose_count');
            
            // Flag to indicate if vaccine is given at birth (0 months)
            $table->boolean('is_birth_dose')->default(false)->after('age_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccines', function (Blueprint $table) {
            $table->dropColumn(['age_schedule', 'is_birth_dose']);
        });
    }
};
