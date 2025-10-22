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
        $this->command->info('🌱 Starting Health Care System Database Seeding...');

        try {
            $this->call([
                

                // Step 2: User accounts for healthcare workers
                UserSeeder::class,

                // Step 3: Vaccine data (must come before immunizations)
                VaccineSeeder::class,

                // Step 4: Patient and maternal health data
                PatientSeeder::class,
                PrenatalRecordSeeder::class,
                PrenatalCheckupSeeder::class,

                // Step 5: Child health data (depends on completed pregnancies)
                ChildRecordSeeder::class,

                // Step 6: Immunization data (depends on vaccines and children)
                ImmunizationSeeder::class,
            ]);

            $this->command->info(' All seeders completed successfully!');
            $this->command->info(' Health Care System for Brgy. Mecolong is ready!');

        } catch (\Exception $e) {
            $this->command->error(' Seeding failed: ' . $e->getMessage());
            $this->command->info(' Try running: php artisan migrate:fresh --seed');
            throw $e;
        }
    }
}
