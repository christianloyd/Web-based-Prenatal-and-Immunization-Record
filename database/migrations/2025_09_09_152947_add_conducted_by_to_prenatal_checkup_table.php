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
            $table->foreignId('conducted_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prenatal_checkups', function (Blueprint $table) {
            $table->dropForeign(['conducted_by']);
            $table->dropColumn('conducted_by');
        });
    }
};
