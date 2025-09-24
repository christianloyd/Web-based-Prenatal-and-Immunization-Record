<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrenatalRecord;
use App\Models\ChildRecord;
use Carbon\Carbon;

class ChildRecordSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating child records for completed pregnancies...');

        // Get completed prenatal records only (these should have children)
        $completedPregnancies = PrenatalRecord::where('status', 'completed')
            ->with('patient')
            ->get();

        if ($completedPregnancies->isEmpty()) {
            $this->command->error('No completed pregnancies found! Please run PrenatalRecordSeeder first.');
            return;
        }

        // Filipino first names
        $maleFirstNames = [
            'Gabriel', 'Lucas', 'Noah', 'Ethan', 'Oliver',
            'Joshua', 'John', 'Mark', 'James', 'Christian',
            'Angelo', 'Kyle', 'Sean', 'Carl', 'Tristan'
        ];

        $femaleFirstNames = [
            'Sophia', 'Isabella', 'Emma', 'Olivia', 'Ava',
            'Ashley', 'Princess', 'Angel', 'Sophia', 'Maria',
            'Cassandra', 'Samantha', 'Christine', 'Andrea', 'Bianca'
        ];

        $middleNames = [
            'James', 'Matthew', 'Alexander', 'Michael', 'David',
            'Miguel', 'Carlo', 'Anthony', 'Patrick', 'Jay',
            'Grace', 'Rose', 'Claire', 'Marie', 'Elizabeth',
            'Mae', 'Joy', 'Nicole', 'Angelica', 'Vincent'
        ];

        $lastNames = [
            'Santos', 'Reyes', 'Cruz', 'Garcia', 'Rodriguez',
            'Martinez', 'Lopez', 'Gonzalez', 'Torres', 'Morales',
            'Dela Cruz', 'Ramos', 'Villanueva', 'Aquino', 'Mendoza'
        ];

        $fatherNames = [
            'Juan Carlos Santos', 'Miguel Angel Reyes', 'Jose Maria Cruz', 'Antonio Luis Garcia',
            'Francisco Javier Rodriguez', 'Carlos Eduardo Martinez', 'Rafael Domingo Lopez',
            'Fernando Santiago Gonzalez', 'Roberto Manuel Torres', 'Diego Alfonso Morales'
        ];

        $birthplaces = [
            'Dumalinao District Hospital, Zamboanga del Sur',
            'Zamboanga del Sur Medical Center',
            'Pagadian City Medical Center',
            'Rural Health Unit, Dumalinao'
        ];

        $createdChildren = 0;

        foreach ($completedPregnancies as $pregnancy) {
            // Calculate realistic birth date based on EDD
            $edd = Carbon::parse($pregnancy->expected_due_date);
            $birthDate = $edd->copy()->addDays(rand(-21, 14)); // Born 3 weeks early to 2 weeks late

            // Skip if birth would be in the future (shouldn't happen with completed pregnancies)
            if ($birthDate->isFuture()) {
                continue;
            }

            // Determine child gender
            $gender = rand(0, 1) === 0 ? 'Male' : 'Female';
            $firstNames = $gender === 'Male' ? $maleFirstNames : $femaleFirstNames;

            // Generate child name
            $firstName = $firstNames[array_rand($firstNames)];
            $middleName = $middleNames[array_rand($middleNames)];
            $lastName = $lastNames[array_rand($lastNames)];

            // Generate realistic birth measurements with better distribution
            $birthWeight = $this->getRealisticBirthWeight(); // 2.0kg to 4.8kg with realistic distribution
            $birthHeight = $this->getRealisticBirthHeight(); // 44cm to 56cm with realistic distribution


            ChildRecord::create([
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'gender' => $gender,
                'birthdate' => $birthDate,
                'birth_weight' => $birthWeight,
                'birth_height' => $birthHeight,
                'birthplace' => $birthplaces[array_rand($birthplaces)],
                'father_name' => $fatherNames[array_rand($fatherNames)],
                'mother_id' => $pregnancy->patient_id,
                'phone_number' => $pregnancy->patient->contact,
                'address' => $pregnancy->patient->address,
                'created_at' => $birthDate->copy()->addDays(1),
                'updated_at' => $birthDate->copy()->addDays(1),
            ]);

            $createdChildren++;
        }

        $this->command->info('Child Record seeder completed successfully!');
        $this->command->info("Generated: {$createdChildren} children from completed pregnancies");
        $this->command->info('All children linked to their biological mothers from Brgy. Mecolong');
    }

    private function getRealisticBirthWeight()
    {
        // More realistic birth weight distribution (grams to kg)
        $weightRanges = [
            [2000, 2499, 5],   // 5% low birth weight
            [2500, 2999, 20],  // 20% lower normal
            [3000, 3499, 50],  // 50% normal range
            [3500, 3999, 20],  // 20% higher normal
            [4000, 4800, 5],   // 5% high birth weight
        ];

        $totalWeight = array_sum(array_column($weightRanges, 2));
        $random = rand(1, $totalWeight);
        $cumulative = 0;

        foreach ($weightRanges as $range) {
            $cumulative += $range[2];
            if ($random <= $cumulative) {
                return round(rand($range[0], $range[1]) / 1000, 3); // Convert to kg
            }
        }

        return 3.2; // Default normal weight
    }

    private function getRealisticBirthHeight()
    {
        // Realistic birth height distribution (cm)
        $heightRanges = [
            [44, 47, 10],  // 10% shorter
            [48, 52, 70],  // 70% normal range
            [53, 56, 20],  // 20% taller
        ];

        $totalWeight = array_sum(array_column($heightRanges, 2));
        $random = rand(1, $totalWeight);
        $cumulative = 0;

        foreach ($heightRanges as $range) {
            $cumulative += $range[2];
            if ($random <= $cumulative) {
                return rand($range[0], $range[1]);
            }
        }

        return 50; // Default normal height
    }

}