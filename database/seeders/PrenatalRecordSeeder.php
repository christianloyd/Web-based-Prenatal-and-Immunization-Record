<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrenatalRecordSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        DB::table('prenatal_records')->delete();

        // Get active patient IDs
        $activePatientIds = DB::table('patients')->whereNull('deleted_at')->pluck('id')->toArray();
        
        if (empty($activePatientIds)) {
            $this->command->error('No active patients found! Please run PatientSeeder first.');
            return;
        }

        // Generate 25 prenatal records (15 active, 10 completed)
        $prenatalRecords = [];
        $recordCounter = 1;

        // Generate 15 active prenatal records
        for ($i = 0; $i < 15; $i++) {
            $patientId = $activePatientIds[$i % count($activePatientIds)];
            $lmpDate = Carbon::now()->subDays(rand(50, 280)); // LMP between 50-280 days ago
            $expectedDueDate = $lmpDate->copy()->addDays(280);
            $gestationalWeeks = Carbon::now()->diffInWeeks($lmpDate);
            $trimester = $this->getTrimester($gestationalWeeks);
            
            $prenatalRecords[] = [
                'formatted_prenatal_id' => 'PR' . str_pad($recordCounter++, 4, '0', STR_PAD_LEFT),
                'patient_id' => $patientId,
                'is_active' => true,
                'last_menstrual_period' => $lmpDate->format('Y-m-d'),
                'expected_due_date' => $expectedDueDate->format('Y-m-d'),
                'gestational_age' => $gestationalWeeks == 1 ? "1 week" : "{$gestationalWeeks} weeks",
                'trimester' => $trimester,
                'gravida' => rand(1, 4),
                'para' => rand(0, 3),
                'medical_history' => $this->getRandomMedicalHistory(),
                'notes' => 'Regular prenatal care',
                'last_visit' => Carbon::now()->subDays(rand(7, 30))->format('Y-m-d'),
                'next_appointment' => Carbon::now()->addDays(rand(7, 30)),
                'status' => $this->getPrenatalStatus($gestationalWeeks),
                'blood_pressure' => rand(100, 130) . '/' . rand(60, 90),
                'weight' => rand(45, 85) + (rand(0, 99) / 100),
                'height' => rand(150, 170),
                'created_at' => Carbon::now()->subDays(rand(30, 200)),
                'updated_at' => Carbon::now()->subDays(rand(1, 30)),
                'deleted_at' => null
            ];
        }

        // Generate 10 completed prenatal records (using patients multiple times for multiple pregnancies)
        for ($i = 0; $i < 10; $i++) {
            $patientId = $activePatientIds[array_rand($activePatientIds)];
            $lmpDate = Carbon::now()->subDays(rand(300, 600)); // Older pregnancies
            $expectedDueDate = $lmpDate->copy()->addDays(280);
            
            $prenatalRecords[] = [
                'formatted_prenatal_id' => 'PR' . str_pad($recordCounter++, 4, '0', STR_PAD_LEFT),
                'patient_id' => $patientId,
                'is_active' => false,
                'last_menstrual_period' => $lmpDate->format('Y-m-d'),
                'expected_due_date' => $expectedDueDate->format('Y-m-d'),
                'gestational_age' => '40 weeks',
                'trimester' => 3,
                'gravida' => rand(1, 4),
                'para' => rand(1, 3),
                'medical_history' => $this->getRandomMedicalHistory(),
                'notes' => 'Pregnancy completed successfully',
                'last_visit' => $expectedDueDate->copy()->subDays(rand(1, 7))->format('Y-m-d'),
                'next_appointment' => null,
                'status' => 'completed',
                'blood_pressure' => rand(110, 140) . '/' . rand(70, 90),
                'weight' => rand(55, 95) + (rand(0, 99) / 100),
                'height' => rand(150, 170),
                'created_at' => $lmpDate->copy()->addDays(30),
                'updated_at' => $expectedDueDate->copy()->addDays(7),
                'deleted_at' => null
            ];
        }

        // Insert prenatal records
        DB::table('prenatal_records')->insert($prenatalRecords);

        $this->command->info('Prenatal Record seeder completed successfully!');
        $this->command->info('Generated: 25 prenatal records (15 active, 10 completed)');
    }

    private function getTrimester($weeks)
    {
        if ($weeks <= 12) return 1;
        if ($weeks <= 27) return 2;
        return 3;
    }

    private function getPrenatalStatus($weeks)
    {
        if ($weeks < 12) return 'normal';
        if ($weeks < 28) return 'normal';
        if ($weeks < 37) return 'monitor';
        if ($weeks >= 40) return 'due';
        return 'normal';
    }

    private function getRandomMedicalHistory()
    {
        $histories = [
            'No significant medical history',
            'Previous cesarean section',
            'History of gestational diabetes',
            'Hypertension, controlled with medication',
            'Previous miscarriage',
            'Anemia, taking iron supplements',
            'No known allergies or medical conditions',
            'History of preterm labor',
            'Thyroid condition, under monitoring'
        ];

        return $histories[array_rand($histories)];
    }
}