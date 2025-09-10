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
        // Add formatted_child_id to child_records table
        Schema::table('child_records', function (Blueprint $table) {
            $table->string('formatted_child_id')->unique()->nullable()->after('id');
        });

        // Add formatted_prenatal_id to prenatal_records table
        Schema::table('prenatal_records', function (Blueprint $table) {
            $table->string('formatted_prenatal_id')->unique()->nullable()->after('id');
        });

        // Add formatted_immunization_id to immunizations table
        Schema::table('immunizations', function (Blueprint $table) {
            $table->string('formatted_immunization_id')->unique()->nullable()->after('id');
        });

        // Add formatted_vaccine_id to vaccines table
        Schema::table('vaccines', function (Blueprint $table) {
            $table->string('formatted_vaccine_id')->unique()->nullable()->after('id');
        });

        // Add formatted_checkup_id to prenatal_checkups table
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->string('formatted_checkup_id')->unique()->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('child_records', function (Blueprint $table) {
            $table->dropColumn('formatted_child_id');
        });

        Schema::table('prenatal_records', function (Blueprint $table) {
            $table->dropColumn('formatted_prenatal_id');
        });

        Schema::table('immunizations', function (Blueprint $table) {
            $table->dropColumn('formatted_immunization_id');
        });

        Schema::table('vaccines', function (Blueprint $table) {
            $table->dropColumn('formatted_vaccine_id');
        });

        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->dropColumn('formatted_checkup_id');
        });
    }
};
