<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VaccineSeeder extends Seeder
{
    public function run(): void
    {
        // Check if vaccines already exist to avoid constraint errors
        $existingVaccines = DB::table('vaccines')->count();

        if ($existingVaccines > 0) {
            $this->command->info('Vaccines already exist. Skipping vaccine seeding to avoid constraint violations.');
            $this->command->info('If you need to reseed vaccines, please run: php artisan migrate:fresh --seed');
            return;
        }

        // Define vaccines matching the actual table structure
        $vaccines = [
            [
                'name' => 'BCG',
                'category' => 'Tuberculosis Prevention',
                'dosage' => '1 dose (0.05ml for infants)',
                'current_stock' => rand(50, 200),
                'min_stock' => 20,
                'expiry_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
                'storage_temp' => '2-8°C',
                'notes' => 'Protects against tuberculosis. Given at birth to 2 months. Intradermal injection.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Pentavalent',
                'category' => 'Multi-disease Prevention',
                'dosage' => '3 doses (0.5ml each)',
                'current_stock' => rand(50, 200),
                'min_stock' => 30,
                'expiry_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
                'storage_temp' => '2-8°C',
                'notes' => 'Protects against Diphtheria, Pertussis, Tetanus, Hepatitis B, and Haemophilus influenzae type b. Given at 6, 10, 14 weeks.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'OPV',
                'category' => 'Polio Prevention',
                'dosage' => '2 doses (2 drops each)',
                'current_stock' => rand(50, 200),
                'min_stock' => 25,
                'expiry_date' => Carbon::now()->addYears(1)->format('Y-m-d'),
                'storage_temp' => '2-8°C',
                'notes' => 'Oral Polio Vaccine. Protects against poliomyelitis. Given at 6, 10 weeks.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'IPV',
                'category' => 'Polio Prevention',
                'dosage' => '2 doses (0.5ml each)',
                'current_stock' => rand(50, 200),
                'min_stock' => 20,
                'expiry_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
                'storage_temp' => '2-8°C',
                'notes' => 'Inactivated Polio Vaccine. Given at 14 weeks and 9 months. Intramuscular injection.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'PCV',
                'category' => 'Pneumonia Prevention',
                'dosage' => '3 doses (0.5ml each)',
                'current_stock' => rand(50, 200),
                'min_stock' => 30,
                'expiry_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
                'storage_temp' => '2-8°C',
                'notes' => 'Pneumococcal Conjugate Vaccine. Protects against pneumonia and meningitis. Given at 6, 10, 14 weeks.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'MCV',
                'category' => 'Measles Prevention',
                'dosage' => '2 doses (0.5ml each)',
                'current_stock' => rand(50, 200),
                'min_stock' => 25,
                'expiry_date' => Carbon::now()->addYears(1)->format('Y-m-d'),
                'storage_temp' => '2-8°C',
                'notes' => 'Measles-Containing Vaccine (MMR). Protects against Measles, Mumps, and Rubella. Given at 9, 12 months.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Vitamin A',
                'category' => 'Vitamin Supplementation',
                'dosage' => '2 doses (capsule/drops)',
                'current_stock' => rand(100, 300),
                'min_stock' => 50,
                'expiry_date' => Carbon::now()->addYears(3)->format('Y-m-d'),
                'storage_temp' => 'Room temperature',
                'notes' => 'Vitamin A supplementation. Prevents vitamin A deficiency. Given at 6, 12 months, then every 6 months.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];

        // Insert vaccines
        DB::table('vaccines')->insert($vaccines);

        $this->command->info('Vaccine seeder completed successfully!');
        $this->command->info('Generated: 7 vaccines with complete immunization schedule information');
        $this->command->info('Vaccines: BCG (1 dose), Pentavalent (3 doses), OPV (2 doses), IPV (2 doses), PCV (3 doses), MCV (2 doses), Vitamin A (2 doses)');
    }
}