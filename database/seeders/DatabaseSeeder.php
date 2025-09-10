<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    // User::factory()->create([
    //     'name' => 'Test User',
    //     'email' => 'test@example.com',
    // ]);
    
    public function run(): void
    {
        $this->call([
            MidwifeSeeder::class,
        ]);
        
        $this->call([
            ChildRecordSeeder::class
        ]);
        
        
    }
}
