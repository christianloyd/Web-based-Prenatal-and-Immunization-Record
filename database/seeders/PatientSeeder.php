<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use Carbon\Carbon;

class PatientSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Creating patients for Brgy. Mecolong, Dumalinao, Zamboanga del Sur...');

        // Filipino mother names with separate first, middle, last names
        $motherNames = [
            ['first' => 'Maria', 'middle' => 'Santos', 'last' => 'Cruz'],
            ['first' => 'Ana', 'middle' => 'Garcia', 'last' => 'Reyes'],
            ['first' => 'Rosa', 'middle' => 'Martinez', 'last' => 'Lopez'],
            ['first' => 'Carmen', 'middle' => 'Rodriguez', 'last' => 'Gonzalez'],
            ['first' => 'Luz', 'middle' => 'Hernandez', 'last' => 'Perez'],
            ['first' => 'Elena', 'middle' => 'Torres', 'last' => 'Rivera'],
            ['first' => 'Sofia', 'middle' => 'Flores', 'last' => 'Morales'],
            ['first' => 'Isabella', 'middle' => 'Gutierrez', 'last' => 'Jimenez'],
            ['first' => 'Camila', 'middle' => 'Vargas', 'last' => 'Herrera'],
            ['first' => 'Valeria', 'middle' => 'Castillo', 'last' => 'Medina'],
            ['first' => 'Gabriela', 'middle' => 'Ortega', 'last' => 'Aguilar'],
            ['first' => 'Daniela', 'middle' => 'Ramos', 'last' => 'Delgado'],
            ['first' => 'Alejandra', 'middle' => 'Vega', 'last' => 'Campos'],
            ['first' => 'Victoria', 'middle' => 'Mendoza', 'last' => 'Fuentes'],
            ['first' => 'Natalia', 'middle' => 'Silva', 'last' => 'Sandoval'],
            ['first' => 'Adriana', 'middle' => 'Guerrero', 'last' => 'Navarro'],
            ['first' => 'Regina', 'middle' => 'Paredes', 'last' => 'Cordova'],
            ['first' => 'Esperanza', 'middle' => 'Rios', 'last' => 'Valdez'],
            ['first' => 'Remedios', 'middle' => 'Salazar', 'last' => 'Espinoza'],
            ['first' => 'Milagros', 'middle' => 'Cabrera', 'last' => 'Cervantes']
        ];

        // Specific addresses within Brgy. Mecolong only
        $addresses = [
            'Purok 1, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
            'Purok 2, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
            'Purok 3, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
            'Purok 4, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
            'Purok 5, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
            'Upper Mecolong, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
            'Lower Mecolong, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
            'Sitio Crossing, Brgy. Mecolong, Dumalinao, Zamboanga del Sur'
        ];

        $occupations = [
            'Housewife', 'Teacher', 'Nurse', 'Store Owner', 'Farmer', 'Seamstress',
            'Barangay Health Worker', 'Day Care Worker', 'Cook', 'Laundrywoman',
            'Market Vendor', 'Beautician', 'Midwife Assistant'
        ];

        // Create patients with realistic maternal health profiles
        foreach ($motherNames as $index => $nameData) {
            // More realistic age distribution for Filipino mothers
            $ageWeighted = [
                18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, // Common ages (18-30)
                31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, // Less common (31-42)
                16, 17, 43, 44 // Rare cases
            ];
            $age = $ageWeighted[array_rand($ageWeighted)];

            // Registration dates should make sense with pregnancy timelines
            $registrationDaysAgo = rand(30, 1095); // 1 month to 3 years ago
            $createdAt = Carbon::now()->subDays($registrationDaysAgo);

            Patient::create([
                'first_name' => $nameData['first'],
                'last_name' => $nameData['last'],
                'name' => $nameData['first'] . ' ' . $nameData['last'], // For compatibility
                'age' => $age,
                'contact' => '+639' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'emergency_contact' => '+639' . str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT),
                'address' => $addresses[array_rand($addresses)],
                'occupation' => $occupations[array_rand($occupations)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info('Patient seeder completed successfully!');
        $this->command->info('Generated: ' . count($motherNames) . ' patients from Brgy. Mecolong');
    }
}