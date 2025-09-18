<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First expand the enum to include both old and new values
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'incomplete', 'upcoming', 'scheduled', 'cancelled', 'rescheduled'])->default('pending')->change();
        });

        // Update existing status values to match new enum
        DB::table('prenatal_checkups')
            ->whereIn('status', ['upcoming', 'scheduled', 'cancelled', 'rescheduled'])
            ->update(['status' => 'pending']);

        // Now safely change the enum to only new values
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'incomplete'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->enum('status', ['completed', 'upcoming', 'cancelled', 'scheduled', 'rescheduled'])->default('upcoming')->change();
        });
    }
};
