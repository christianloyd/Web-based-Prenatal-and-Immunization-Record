<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE child_records MODIFY phone_number VARCHAR(20) NULL");
            return;
        }

        if ($driver === 'sqlite') {
            Schema::table('child_records', function ($table) {
                $table->string('phone_number', 20)->nullable()->change();
            });
            return;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE child_records MODIFY phone_number VARCHAR(20) NOT NULL");
            return;
        }

        if ($driver === 'sqlite') {
            Schema::table('child_records', function ($table) {
                $table->string('phone_number', 20)->nullable(false)->change();
            });
            return;
        }
    }
};
