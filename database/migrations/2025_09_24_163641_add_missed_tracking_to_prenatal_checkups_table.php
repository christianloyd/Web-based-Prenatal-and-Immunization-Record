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
            $table->timestamp('missed_date')->nullable()->after('status');
            $table->string('missed_reason')->nullable()->after('missed_date');
            $table->boolean('auto_missed')->default(false)->after('missed_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->dropColumn(['missed_date', 'missed_reason', 'auto_missed']);
        });
    }
};
