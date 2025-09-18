<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\ChildRecord;
use Carbon\Carbon;

class ChildRecordSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('child_records')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Get available mothers (patients) - use existing patients as mothers
        $mothers = Patient::inRandomOrder()->limit(15)->get();

        if ($mothers->isEmpty()) {
            $this->command->error('No patients found! Please run Patient seeder first.');
            return;
        }

        // Filipino child names
        $maleNames = [
            'Gabriel James', 'Lucas Matthew', 'Noah Alexander', 'Ethan Michael', 'Oliver David',
            'Joshua Miguel', 'John Carlo', 'Mark Anthony', 'James Patrick', 'Christian Jay',
            'Angelo Miguel', 'Kyle Matthew', 'Sean Gabriel', 'Carl Vincent', 'Tristan Jose'
        ];

        $femaleNames = [
            'Sophia Grace', 'Isabella Rose', 'Emma Claire', 'Olivia Marie', 'Ava Elizabeth',
            'Ashley Mae', 'Princess Joy', 'Angel Grace', 'Sophia Marie', 'Maria Angelica',
            'Cassandra Joy', 'Samantha Mae', 'Christine Joy', 'Andrea Nicole', 'Bianca Marie'
        ];

        $fatherNames = [
            'Juan Carlos Santos', 'Miguel Angel Reyes', 'Jose Maria Cruz', 'Antonio Luis Garcia',
            'Francisco Javier Rodriguez', 'Carlos Eduardo Martinez', 'Rafael Domingo Lopez',
            'Fernando Santiago Gonzalez', 'Roberto Manuel Torres', 'Diego Alfonso Morales'
        ];

        $addresses = [
            'Purok 1, Barangay San Jose, Antipolo City',
            'Purok 2, Barangay Santa Cruz, Antipolo City',
            'Purok 3, Barangay San Roque, Antipolo City',
            'Purok 4, Barangay San Isidro, Antipolo City',
            'Purok 5, Barangay San Juan, Antipolo City'
        ];

        $birthplaces = [
            'Antipolo City General Hospital',
            'Rizal Medical Center',
            'Our Lady of Peace Hospital',
            'Antipolo Doctors Hospital'
        ];

        $childRecords = [];

        // Create 10 child records with varying ages for immunization testing
        for ($i = 0; $i < 10; $i++) {
            $gender = rand(0, 1) === 0 ? 'Male' : 'Female';
            $names = $gender === 'Male' ? $maleNames : $femaleNames;
            $childName = $names[array_rand($names)];

            // Create children of different ages for realistic immunization scenarios
            $ageRanges = [
                Carbon::now()->subMonths(2),   // 2 months old - early immunizations
                Carbon::now()->subMonths(4),   // 4 months old - mid primary series
                Carbon::now()->subMonths(6),   // 6 months old - completing primary series
                Carbon::now()->subMonths(9),   // 9 months old - measles age
                Carbon::now()->subMonths(12),  // 12 months old - full series
                Carbon::now()->subMonths(18),  // 18 months old - all complete
                Carbon::now()->subMonths(24),  // 2 years old - fully immunized
                Carbon::now()->subMonths(1),   // 1 month old - just started
                Carbon::now()->subMonths(3),   // 3 months old - partial
                Carbon::now()->subWeeks(2),    // 2 weeks old - newborn
            ];

            $birthdate = $ageRanges[$i]->copy();

            // Random mother from available patients
            $mother = $mothers->random();

            // Generate realistic birth measurements
            $birthWeight = round(rand(2500, 4500) / 1000, 3); // 2.5kg to 4.5kg
            $birthHeight = round(rand(4500, 5500) / 100, 2); // 45cm to 55cm

            // Generate phone number
            $phoneNumber = '+639' . rand(100000000, 999999999);

            $childRecords[] = [
                'child_name' => $childName,
                'gender' => $gender,
                'birthdate' => $birthdate->format('Y-m-d H:i:s'),
                'birth_weight' => $birthWeight,
                'birth_height' => $birthHeight,
                'birthplace' => $birthplaces[array_rand($birthplaces)],
                'father_name' => $fatherNames[array_rand($fatherNames)],
                'mother_id' => $mother->id,
                'phone_number' => $phoneNumber,
                'address' => $addresses[array_rand($addresses)],
                'created_at' => $birthdate->copy()->addDays(rand(0, 3))->format('Y-m-d H:i:s'),
                'updated_at' => $birthdate->copy()->addDays(rand(0, 3))->format('Y-m-d H:i:s')
            ];
        }

        // Insert child records
        DB::table('child_records')->insert($childRecords);

        $this->command->info('✅ ChildRecord seeder completed successfully!');
        $this->command->info('Generated: ' . count($childRecords) . ' child records');
        $this->command->info('📅 Children of various ages for immunization testing');
        $this->command->info('👥 Using ' . $mothers->count() . ' different mothers');
        $this->command->info('🎯 Ages: 2 weeks to 24 months for realistic immunization scenarios');
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