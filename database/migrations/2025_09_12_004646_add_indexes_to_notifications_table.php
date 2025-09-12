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
        Schema::table('notifications', function (Blueprint $table) {
            // Add composite index for notifiable_type, notifiable_id, and read_at
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'idx_notifications_user_read');
            
            // Add index for created_at for ordering
            $table->index('created_at', 'idx_notifications_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Drop the indexes
            $table->dropIndex('idx_notifications_user_read');
            $table->dropIndex('idx_notifications_created_at');
        });
    }
};
