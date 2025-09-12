<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChildRecordSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::table('child_records')->delete();

        // Get completed prenatal records to find mothers
        $completedMothers = DB::table('prenatal_records')
            ->join('patients', 'prenatal_records.patient_id', '=', 'patients.id')
            ->where('prenatal_records.is_active', false) // completed pregnancies
            ->whereNull('patients.deleted_at') // active patients only
            ->select('patients.*')
            ->distinct()
            ->get()
            ->toArray();

        if (empty($completedMothers)) {
            $this->command->error('No completed prenatal mothers found! Please run PrenatalRecordSeeder first.');
            return;
        }

        // Filipino child names
        $maleNames = [
            'Joshua Miguel', 'John Carlo', 'Mark Anthony', 'James Patrick', 'Christian Jay',
            'Angelo Miguel', 'Kyle Matthew', 'Sean Gabriel', 'Carl Vincent', 'Tristan Jose',
            'Nathan Luis', 'Isaiah Daniel', 'Adrian Paul', 'Ethan Miguel', 'Lucas Gabriel'
        ];

        $femaleNames = [
            'Ashley Mae', 'Princess Joy', 'Angel Grace', 'Sophia Marie', 'Maria Angelica',
            'Isabella Rose', 'Cassandra Joy', 'Samantha Mae', 'Christine Joy', 'Andrea Nicole',
            'Bianca Marie', 'Camille Grace', 'Danielle Rose', 'Gabrielle Mae', 'Hannah Grace'
        ];

        $childRecords = [];
        $recordCounter = 1;

        foreach ($completedMothers as $mother) {
            // 1-2 children per mother
            $numberOfChildren = rand(1, 2);
            
            for ($i = 0; $i < $numberOfChildren; $i++) {
                $gender = ['Male', 'Female'][rand(0, 1)];
                $childName = $gender === 'Male' 
                    ? $maleNames[array_rand($maleNames)] 
                    : $femaleNames[array_rand($femaleNames)];

                // Child age in months (0-60 months = 0-5 years)
                $ageInMonths = rand(0, 60);
                $birthDate = Carbon::now()->subMonths($ageInMonths);

                // Generate father name from mother's surname or create random
                $motherNameParts = explode(' ', $mother->name);
                $possibleFatherNames = [
                    'Juan Carlos', 'Miguel Santos', 'Roberto Cruz', 'Fernando Garcia',
                    'Antonio Rodriguez', 'Ricardo Martinez', 'Eduardo Lopez', 'Carlos Reyes',
                    'Manuel Torres', 'Francisco Gonzales', 'Pedro Hernandez', 'Luis Morales'
                ];
                $fatherName = $possibleFatherNames[array_rand($possibleFatherNames)];
                
                // Add mother's surname to father if available
                if (count($motherNameParts) >= 2) {
                    $fatherName .= ' ' . end($motherNameParts);
                }

                $childRecords[] = [
                    'formatted_child_id' => 'CH' . str_pad($recordCounter++, 4, '0', STR_PAD_LEFT),
                    'child_name' => $childName,
                    'gender' => $gender,
                    'birth_height' => $this->generateBirthHeight(),
                    'birth_weight' => $this->generateBirthWeight(),
                    'birthdate' => $birthDate->format('Y-m-d'),
                    'birthplace' => 'General Santos City',
                    'address' => $mother->address ?? 'General Santos City',
                    'father_name' => $fatherName,
                    'phone_number' => $mother->contact ?? '09' . rand(100000000, 999999999),
                    'mother_name' => $mother->name,
                    'mother_id' => $mother->id,
                    'created_at' => Carbon::now()->subDays(rand(1, 365)),
                    'updated_at' => Carbon::now()->subDays(rand(1, 30))
                ];
            }
        }

        // Insert child records
        DB::table('child_records')->insert($childRecords);

        $this->command->info('ChildRecord seeder completed successfully!');
        $this->command->info('Generated: ' . count($childRecords) . ' child records from ' . count($completedMothers) . ' completed prenatal mothers');
        $this->command->info('Children age range: 0-60 months (0-5 years)');
    }

    private function generateBirthHeight(): float
    {
        // Normal birth height range: 45-55 cm
        return round(rand(4500, 5500) / 100, 2);
    }

    private function generateBirthWeight(): float
    {
        // Normal birth weight range: 2.5-4.5 kg
        return round(rand(2500, 4500) / 1000, 3);
    }
}