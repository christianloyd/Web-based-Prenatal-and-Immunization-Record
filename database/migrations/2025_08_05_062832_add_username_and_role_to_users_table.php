<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('gender', ['male','female','other'])->after('name');
            $table->unsignedTinyInteger('age')->after('gender');
            $table->string('username')->nullable()->after('age');
            $table->string('contact_number')->nullable()->after('username');
            $table->string('address')->nullable()->after('contact_number');
            $table->enum('role', ['midwife','bhw'])->default('midwife')->after('address');
            $table->boolean('is_active')->default(true)->after('role');
        });
        
        // Update existing users with default usernames and other fields
        DB::statement("UPDATE users SET username = COALESCE(SUBSTRING_INDEX(email, '@', 1), CONCAT('user', id)) WHERE username IS NULL OR username = ''");
        DB::statement("UPDATE users SET gender = 'male' WHERE gender IS NULL OR gender = ''");
        DB::statement("UPDATE users SET age = 25 WHERE age IS NULL OR age = 0");
        
        // Now add the unique constraint
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gender', 'age', 'username', 'contact_number', 'address', 'role', 'is_active']);
        });
    }
};