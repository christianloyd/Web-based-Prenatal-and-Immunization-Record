<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('immunizations', function (Blueprint $table) {
            // Add vaccine_id column
            $table->unsignedBigInteger('vaccine_id')->nullable()->after('child_record_id');
            
            // Add foreign key constraint
            $table->foreign('vaccine_id')
                  ->references('id')
                  ->on('vaccines')
                  ->onDelete('restrict'); // Prevent deletion of vaccines with scheduled immunizations
            
            // Add index for better performance
            $table->index('vaccine_id');
        });
    }

    public function down(): void
    {
        Schema::table('immunizations', function (Blueprint $table) {
            $table->dropForeign(['vaccine_id']);
            $table->dropColumn('vaccine_id');
        });
    }
};