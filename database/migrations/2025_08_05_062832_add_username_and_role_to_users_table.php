<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('users');          // get rid of the old table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');        // new
            $table->enum('gender', ['male','female','other']);
            $table->unsignedTinyInteger('age');
            $table->string('username')->unique();
            $table->string('contact_number')->nullable();  // <-- added
            $table->string('address')->nullable();            // user address
            $table->enum('role', ['midwife','bhw'])->default('midwife');
            $table->boolean('is_active')->default(true); // no 'after' here
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};