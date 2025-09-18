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
        // First expand the enum to include all old and new values
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'incomplete', 'upcoming', 'done'])->default('pending')->change();
        });

        // Now update existing status values to new values
        DB::table('prenatal_checkups')
            ->where('status', 'pending')
            ->update(['status' => 'upcoming']);

        DB::table('prenatal_checkups')
            ->where('status', 'completed')
            ->update(['status' => 'done']);

        DB::table('prenatal_checkups')
            ->where('status', 'incomplete')
            ->update(['status' => 'upcoming']);

        // Finally change the enum to only allow 'upcoming' and 'done'
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->enum('status', ['upcoming', 'done'])->default('upcoming')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to the previous enum values
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'incomplete'])->default('pending')->change();
        });

        // Revert the status values
        DB::table('prenatal_checkups')
            ->where('status', 'upcoming')
            ->update(['status' => 'pending']);

        DB::table('prenatal_checkups')
            ->where('status', 'done')
            ->update(['status' => 'completed']);
    }
};
