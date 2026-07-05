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
        Schema::table('maternal_immunizations', function (Blueprint $table) {
            $table->string('external_vaccine_lot')->nullable()->after('is_external');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maternal_immunizations', function (Blueprint $table) {
            $table->dropColumn('external_vaccine_lot');
        });
    }
};
