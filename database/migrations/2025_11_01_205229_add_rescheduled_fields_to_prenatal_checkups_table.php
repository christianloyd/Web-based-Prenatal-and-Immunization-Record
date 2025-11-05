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
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->boolean('rescheduled')->default(false)->after('auto_missed');
            $table->unsignedBigInteger('rescheduled_to_checkup_id')->nullable()->after('rescheduled');

            // Add foreign key constraint (optional, but good practice)
            $table->foreign('rescheduled_to_checkup_id')
                  ->references('id')
                  ->on('prenatal_checkups')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->dropForeign(['rescheduled_to_checkup_id']);
            $table->dropColumn(['rescheduled', 'rescheduled_to_checkup_id']);
        });
    }
};
