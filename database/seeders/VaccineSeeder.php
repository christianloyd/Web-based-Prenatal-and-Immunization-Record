<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vaccine;
use Carbon\Carbon;

class VaccineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vaccines = [
            // Routine Immunization Vaccines (matching Immunization model names)
            [
                'name' => 'BCG',
                'category' => 'Routine Immunization',
                'dosage' => '0.1ml',
                'current_stock' => 120,
                'min_stock' => 15,
                'expiry_date' => Carbon::now()->addMonths(18),
                'storage_temp' => '2-8°C',
                'notes' => 'Bacillus Calmette-Guérin vaccine for tuberculosis prevention',
            ],
            [
                'name' => 'Hepatitis B',
                'category' => 'Routine Immunization',
                'dosage' => '0.5ml',
                'current_stock' => 150,
                'min_stock' => 20,
                'expiry_date' => Carbon::now()->addMonths(18),
                'storage_temp' => '2-8°C',
                'notes' => 'For newborns and high-risk adults',
            ],
            [
                'name' => 'DPT',
                'category' => 'Routine Immunization',
                'dosage' => '0.5ml',
                'current_stock' => 85,
                'min_stock' => 15,
                'expiry_date' => Carbon::now()->addMonths(20),
                'storage_temp' => '2-8°C',
                'notes' => 'Diphtheria, Pertussis, and Tetanus combination vaccine',
            ],
            [
                'name' => 'OPV',
                'category' => 'Routine Immunization',
                'dosage' => '2 drops',
                'current_stock' => 200,
                'min_stock' => 25,
                'expiry_date' => Carbon::now()->addMonths(15),
                'storage_temp' => '2-8°C',
                'notes' => 'Oral Polio Vaccine - live attenuated',
            ],
            [
                'name' => 'IPV',
                'category' => 'Routine Immunization',
                'dosage' => '0.5ml',
                'current_stock' => 90,
                'min_stock' => 15,
                'expiry_date' => Carbon::now()->addMonths(22),
                'storage_temp' => '2-8°C',
                'notes' => 'Inactivated Polio Vaccine',
            ],
            [
                'name' => 'Hib',
                'category' => 'Routine Immunization',
                'dosage' => '0.5ml',
                'current_stock' => 70,
                'min_stock' => 12,
                'expiry_date' => Carbon::now()->addMonths(16),
                'storage_temp' => '2-8°C',
                'notes' => 'Haemophilus influenzae type b vaccine',
            ],
            [
                'name' => 'PCV',
                'category' => 'Routine Immunization',
                'dosage' => '0.5ml',
                'current_stock' => 60,
                'min_stock' => 10,
                'expiry_date' => Carbon::now()->addMonths(14),
                'storage_temp' => '2-8°C',
                'notes' => 'Pneumococcal conjugate vaccine',
            ],
            [
                'name' => 'MMR',
                'category' => 'Routine Immunization',
                'dosage' => '0.5ml',
                'current_stock' => 75,
                'min_stock' => 15,
                'expiry_date' => Carbon::now()->addMonths(24),
                'storage_temp' => '2-8°C',
                'notes' => 'Measles, Mumps, and Rubella - live attenuated vaccine',
            ],
            [
                'name' => 'Varicella',
                'category' => 'Routine Immunization',
                'dosage' => '0.5ml',
                'current_stock' => 45,
                'min_stock' => 8,
                'expiry_date' => Carbon::now()->addMonths(20),
                'storage_temp' => '2-8°C',
                'notes' => 'Chickenpox vaccine - live attenuated',
            ],
            [
                'name' => 'Hepatitis A',
                'category' => 'Routine Immunization',
                'dosage' => '0.5ml',
                'current_stock' => 55,
                'min_stock' => 10,
                'expiry_date' => Carbon::now()->addMonths(18),
                'storage_temp' => '2-8°C',
                'notes' => 'Hepatitis A vaccine for endemic areas',
            ],
            [
                'name' => 'Td',
                'category' => 'Routine Immunization',
                'dosage' => '0.5ml',
                'current_stock' => 8,  // Low stock
                'min_stock' => 20,
                'expiry_date' => Carbon::now()->addDays(25), // Expiring soon
                'storage_temp' => '2-8°C',
                'notes' => 'Tetanus-Diphtheria toxoid, booster every 10 years',
            ],
            [
                'name' => 'Tdap',
                'category' => 'Routine Immunization',
                'dosage' => '0.5ml',
                'current_stock' => 40,
                'min_stock' => 12,
                'expiry_date' => Carbon::now()->addMonths(16),
                'storage_temp' => '2-8°C',
                'notes' => 'Tetanus, Diphtheria, and Pertussis booster',
            ],
            
            // Seasonal Vaccines
            [
                'name' => 'Influenza',
                'category' => 'Seasonal',
                'dosage' => '0.5ml',
                'current_stock' => 0, // Out of stock
                'min_stock' => 30,
                'expiry_date' => Carbon::now()->addMonths(6),
                'storage_temp' => '2-8°C',
                'notes' => 'Annual seasonal flu vaccination recommended',
            ],
            
            // COVID-19 Vaccines
            [
                'name' => 'COVID-19 mRNA',
                'category' => 'COVID-19',
                'dosage' => '0.3ml',
                'current_stock' => 5, // Low stock
                'min_stock' => 25,
                'expiry_date' => Carbon::now()->addMonths(8),
                'storage_temp' => '2-8°C',
                'notes' => 'mRNA COVID-19 vaccine, store in refrigerator',
            ],
            
            // Travel Vaccines
            [
                'name' => 'Yellow Fever',
                'category' => 'Travel',
                'dosage' => '0.5ml',
                'current_stock' => 40,
                'min_stock' => 10,
                'expiry_date' => Carbon::now()->addMonths(36),
                'storage_temp' => '2-8°C',
                'notes' => 'Required for travel to endemic areas, valid for 10 years',
            ],
        ];

        foreach ($vaccines as $vaccine) {
            $createdVaccine = Vaccine::create($vaccine);
            
            // Create initial stock transaction if stock > 0
            if ($vaccine['current_stock'] > 0) {
                $createdVaccine->stockTransactions()->create([
                    'transaction_type' => 'in',
                    'quantity' => $vaccine['current_stock'],
                    'previous_stock' => 0,
                    'new_stock' => $vaccine['current_stock'],
                    'reason' => 'Initial stock entry from seeder'
                ]);
            }
        }
        
        $this->command->info('Vaccine seeder completed successfully!');
        $this->command->info('Created ' . count($vaccines) . ' vaccine records with stock transactions.');
    }
}