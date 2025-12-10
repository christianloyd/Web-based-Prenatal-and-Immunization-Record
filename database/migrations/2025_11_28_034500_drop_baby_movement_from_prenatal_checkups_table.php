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
            if (Schema::hasColumn('prenatal_checkups', 'baby_movement')) {
                $table->dropColumn('baby_movement');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->enum('baby_movement', ['active', 'normal', 'less'])->nullable();
        });
    }
};
