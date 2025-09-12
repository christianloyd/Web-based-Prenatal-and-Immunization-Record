<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PatientSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        DB::table('patients')->delete();

        // Sample Filipino names for patients
        $femaleNames = [
            'Maria Santos', 'Ana Cruz', 'Rosa Reyes', 'Carmen Garcia', 'Luz Hernandez',
            'Elena Rodriguez', 'Sofia Martinez', 'Isabella Lopez', 'Camila Gonzales', 'Valeria Perez',
            'Gabriela Rivera', 'Daniela Torres', 'Alejandra Flores', 'Victoria Morales', 'Natalia Gutierrez',
            'Adriana Jimenez', 'Regina Castillo', 'Esperanza Vargas', 'Remedios Aguilar', 'Milagros Delgado'
        ];

        $addresses = [
            'Purok Crossing, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
            'Purok Upper, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
            'Purok Lower, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
            'Purok Francisco, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
            'Purok Makurat, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
            'Purok Salinsing 1, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
            'Purok Salinging 2, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',

        ];

        $occupations = [
            'Housewife', 'Teacher', 'Nurse', 'Sales Associate', 'Office Worker',
            'Cashier', 'Manager', 'Entrepreneur', 'Student', 'Freelancer'
        ];

        // Generate 20 patients
        $patients = [];
        for ($i = 1; $i <= 20; $i++) {
            $createdAt = Carbon::now()->subDays(rand(30, 365));
            
            $patient = [
                'formatted_patient_id' => 'P' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => $femaleNames[$i - 1],
                'age' => rand(18, 40),
                'contact' => '09' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'emergency_contact' => '09' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'address' => $addresses[array_rand($addresses)],
                'occupation' => $occupations[array_rand($occupations)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'deleted_at' => null
            ];

            // Make 5 patients inactive (soft deleted) - last 5 patients
            if ($i > 15) {
                $patient['deleted_at'] = Carbon::now()->subDays(rand(1, 30));
            }

            $patients[] = $patient;
        }

        // Insert patients
        DB::table('patients')->insert($patients);

        $this->command->info('Patient seeder completed successfully!');
        $this->command->info('Generated: 20 patients (15 active, 5 inactive)');
    }
}