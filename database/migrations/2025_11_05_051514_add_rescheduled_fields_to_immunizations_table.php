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
        Schema::table('immunizations', function (Blueprint $table) {
            // Add rescheduled boolean flag
            $table->boolean('rescheduled')->default(false)->after('status');

            // Add foreign key to link to the new rescheduled immunization
            $table->unsignedBigInteger('rescheduled_to_immunization_id')->nullable()->after('rescheduled');

            // Add foreign key constraint
            $table->foreign('rescheduled_to_immunization_id')
                  ->references('id')
                  ->on('immunizations')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('immunizations', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['rescheduled_to_immunization_id']);

            // Drop columns
            $table->dropColumn(['rescheduled', 'rescheduled_to_immunization_id']);
        });
    }
};
