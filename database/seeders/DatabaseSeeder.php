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
            // Keep existing midwife seeder
            MidwifeSeeder::class,
            
            // Add comprehensive healthcare data seeders
            VaccineSeeder::class, // Must come first for vaccine references
            PatientSeeder::class,
            ComprehensivePrenatalRecordSeeder::class,
            ComprehensiveChildRecordSeeder::class,
            ComprehensiveImmunizationSeeder::class,
            ComprehensivePrenatalCheckupSeeder::class,
        ]);
    }
}
