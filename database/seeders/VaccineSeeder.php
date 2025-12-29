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
                'dose_count' => 1,
                'age_schedule' => json_encode([
                    'doses' => [
                        ['dose_number' => 1, 'age' => 0, 'unit' => 'months', 'label' => '1st Dose']
                    ]
                ]),
                'is_birth_dose' => true,
                'current_stock' => rand(50, 200),
                'min_stock' => 20,
                'expiry_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
                'storage_temp' => '2-8°C',
                'notes' => 'Protects against tuberculosis. Given at birth. Intradermal injection.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'IPV',
                'category' => 'Polio Prevention',
                'dosage' => '2 doses (0.5ml each)',
                'dose_count' => 2,
                'age_schedule' => json_encode([
                    'doses' => [
                        ['dose_number' => 1, 'age' => 14, 'unit' => 'weeks', 'label' => '1st Dose'],
                        ['dose_number' => 2, 'age' => 9, 'unit' => 'months', 'label' => '2nd Dose']
                    ]
                ]),
                'is_birth_dose' => false,
                'current_stock' => rand(50, 200),
                'min_stock' => 20,
                'expiry_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
                'storage_temp' => '2-8°C',
                'notes' => 'Inactivated Polio Vaccine. Given at 14 weeks and 9 months. Intramuscular injection.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'MCV',
                'category' => 'Measles Prevention',
                'dosage' => '2 doses (0.5ml each)',
                'dose_count' => 2,
                'age_schedule' => json_encode([
                    'doses' => [
                        ['dose_number' => 1, 'age' => 9, 'unit' => 'months', 'label' => '1st Dose'],
                        ['dose_number' => 2, 'age' => 12, 'unit' => 'months', 'label' => '2nd Dose']
                    ]
                ]),
                'is_birth_dose' => false,
                'current_stock' => rand(50, 200),
                'min_stock' => 25,
                'expiry_date' => Carbon::now()->addYears(1)->format('Y-m-d'),
                'storage_temp' => '2-8°C',
                'notes' => 'Measles-Containing Vaccine (MMR). Protects against Measles, Mumps, and Rubella. Given at 9, 12 months.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'OPV',
                'category' => 'Polio Prevention',
                'dosage' => '2 doses (2 drops each)',
                'dose_count' => 2,
                'age_schedule' => json_encode([
                    'doses' => [
                        ['dose_number' => 1, 'age' => 6, 'unit' => 'weeks', 'label' => '1st Dose'],
                        ['dose_number' => 2, 'age' => 10, 'unit' => 'weeks', 'label' => '2nd Dose']
                    ]
                ]),
                'is_birth_dose' => false,
                'current_stock' => rand(50, 200),
                'min_stock' => 25,
                'expiry_date' => Carbon::now()->addYears(1)->format('Y-m-d'),
                'storage_temp' => '2-8°C',
                'notes' => 'Oral Polio Vaccine. Protects against poliomyelitis. Given at 6, 10 weeks.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'PCV',
                'category' => 'Pneumonia Prevention',
                'dosage' => '3 doses (0.5ml each)',
                'dose_count' => 3,
                'age_schedule' => json_encode([
                    'doses' => [
                        ['dose_number' => 1, 'age' => 6, 'unit' => 'weeks', 'label' => '1st Dose'],
                        ['dose_number' => 2, 'age' => 10, 'unit' => 'weeks', 'label' => '2nd Dose'],
                        ['dose_number' => 3, 'age' => 14, 'unit' => 'weeks', 'label' => '3rd Dose']
                    ]
                ]),
                'is_birth_dose' => false,
                'current_stock' => rand(50, 200),
                'min_stock' => 30,
                'expiry_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
                'storage_temp' => '2-8°C',
                'notes' => 'Pneumococcal Conjugate Vaccine. Protects against pneumonia and meningitis. Given at 6, 10, 14 weeks.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Pentavalent',
                'category' => 'Multi-disease Prevention',
                'dosage' => '3 doses (0.5ml each)',
                'dose_count' => 3,
                'age_schedule' => json_encode([
                    'doses' => [
                        ['dose_number' => 1, 'age' => 6, 'unit' => 'weeks', 'label' => '1st Dose'],
                        ['dose_number' => 2, 'age' => 10, 'unit' => 'weeks', 'label' => '2nd Dose'],
                        ['dose_number' => 3, 'age' => 14, 'unit' => 'weeks', 'label' => '3rd Dose']
                    ]
                ]),
                'is_birth_dose' => false,
                'current_stock' => rand(50, 200),
                'min_stock' => 30,
                'expiry_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
                'storage_temp' => '2-8°C',
                'notes' => 'Protects against Diphtheria, Pertussis, Tetanus, Hepatitis B, and Haemophilus influenzae type b. Given at 6, 10, 14 weeks.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Hepatitis B',
                'category' => 'Hepatitis Prevention',
                'dosage' => '1 dose (0.5ml)',
                'dose_count' => 1,
                'age_schedule' => json_encode([
                    'doses' => [
                        ['dose_number' => 1, 'age' => 0, 'unit' => 'months', 'label' => '1st Dose']
                    ]
                ]),
                'is_birth_dose' => true,
                'current_stock' => rand(50, 200),
                'min_stock' => 30,
                'expiry_date' => Carbon::now()->addYears(2)->format('Y-m-d'),
                'storage_temp' => '2-8°C',
                'notes' => 'Protects against Hepatitis B. Given at birth. Intramuscular injection.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Vitamin A',
                'category' => 'Vitamin Supplementation',
                'dosage' => '2 doses (capsule/drops)',
                'dose_count' => 2,
                'age_schedule' => json_encode([
                    'doses' => [
                        ['dose_number' => 1, 'age' => 6, 'unit' => 'months', 'label' => '1st Dose'],
                        ['dose_number' => 2, 'age' => 12, 'unit' => 'months', 'label' => '2nd Dose']
                    ]
                ]),
                'is_birth_dose' => false,
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
        $this->command->info('Generated: 8 vaccines with complete DOH immunization schedule information');
        $this->command->info('Vaccines: BCG (birth), Hepatitis B (birth), Pentavalent (3 doses), OPV (2 doses), IPV (2 doses), PCV (3 doses), MCV (2 doses), Vitamin A (2 doses)');
    }
}