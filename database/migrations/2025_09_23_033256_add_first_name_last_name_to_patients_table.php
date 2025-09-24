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
        Schema::table('patients', function (Blueprint $table) {
            // Add first_name and last_name fields after the name field
            $table->string('first_name')->after('name')->nullable();
            $table->string('last_name')->after('first_name')->nullable();

            // Add indexes for the new fields
            $table->index(['first_name']);
            $table->index(['last_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Drop the new fields
            $table->dropIndex(['first_name']);
            $table->dropIndex(['last_name']);
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
