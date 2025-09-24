<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\PrenatalRecord;
use Carbon\Carbon;

class PrenatalRecordSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Creating prenatal records following system logic...');

        // Get all patients
        $patients = Patient::all();

        if ($patients->isEmpty()) {
            $this->command->error('No patients found! Please run PatientSeeder first.');
            return;
        }

        $activeRecords = 0;
        $completedRecords = 0;

        foreach ($patients as $patient) {
            // Determine pregnancy journey for this patient
            $journeyType = $this->determinePregnancyJourney($patient->id);

            switch ($journeyType) {
                case 'currently_pregnant':
                    $this->createCurrentPregnancy($patient);
                    $activeRecords++;
                    break;

                case 'completed_pregnancy':
                    $this->createCompletedPregnancy($patient);
                    $completedRecords++;
                    break;

                case 'multiple_pregnancies':
                    $this->createMultiplePregnancies($patient);
                    $completedRecords += 2;
                    break;

                case 'high_risk_pregnancy':
                    $this->createHighRiskPregnancy($patient);
                    $activeRecords++;
                    break;
            }
        }

        $this->command->info('Prenatal Record seeder completed successfully!');
        $this->command->info("Generated: {$activeRecords} active, {$completedRecords} completed prenatal records");
    }

    private function determinePregnancyJourney($patientId)
    {
        // Distribute journey types realistically
        $rand = $patientId % 10;

        if ($rand < 3) return 'currently_pregnant';      // 30% currently pregnant
        if ($rand < 6) return 'completed_pregnancy';     // 30% completed pregnancy
        if ($rand < 8) return 'multiple_pregnancies';    // 20% multiple pregnancies
        if ($rand < 9) return 'high_risk_pregnancy';     // 10% high-risk
        return 'completed_pregnancy';                     // 10% completed
    }

    private function createCurrentPregnancy($patient)
    {
        // More realistic current pregnancy timing (4-36 weeks)
        $currentWeeks = rand(4, 36);
        $lmp = Carbon::now()->subWeeks($currentWeeks);
        $edd = $lmp->copy()->addDays(280); // Standard 40-week pregnancy

        PrenatalRecord::create([
            'patient_id' => $patient->id,
            'last_menstrual_period' => $lmp,
            'expected_due_date' => $edd,
            'gravida' => $this->getRealisticGravida($patient->age),
            'para' => $this->getRealisticPara($patient->age),
            'medical_history' => $this->getRandomMedicalHistory(),
            'notes' => 'Regular prenatal care ongoing. Patient attending scheduled checkups.',
            'status' => $this->getPrenatalStatus($currentWeeks),
            'created_at' => $lmp->copy()->addWeeks(4), // Registered 4 weeks after LMP
            'updated_at' => Carbon::now()->subDays(rand(1, 7)),
        ]);
    }

    private function createCompletedPregnancy($patient)
    {
        // Pregnancy should align with patient's current age and registration
        $monthsAgo = rand(6, 60); // 6 months to 5 years ago
        $lmp = Carbon::now()->subMonths($monthsAgo)->addDays(280); // Start from conception
        $lmp = $lmp->subDays(280); // Go back to actual LMP
        $edd = $lmp->copy()->addDays(280);
        $deliveryDate = $edd->copy()->addDays(rand(-21, 14)); // Born 3 weeks early to 2 weeks late

        PrenatalRecord::create([
            'patient_id' => $patient->id,
            'last_menstrual_period' => $lmp,
            'expected_due_date' => $edd,
            'gravida' => rand(1, 4),
            'para' => rand(1, 3),
            'medical_history' => $this->getRandomMedicalHistory(),
            'notes' => 'Pregnancy completed successfully. Baby delivered on ' . $deliveryDate->format('M j, Y'),
            'status' => 'completed',
            'created_at' => $lmp->copy()->addWeeks(6),
            'updated_at' => $deliveryDate->copy()->addDays(7),
        ]);
    }

    private function createMultiplePregnancies($patient)
    {
        // First pregnancy (older)
        $lmp1 = Carbon::now()->subYears(rand(3, 6));
        $edd1 = $lmp1->copy()->addDays(280);

        PrenatalRecord::create([
            'patient_id' => $patient->id,
            'last_menstrual_period' => $lmp1,
            'expected_due_date' => $edd1,
            'gravida' => 1,
            'para' => 0,
            'medical_history' => 'First pregnancy, no complications',
            'notes' => 'First pregnancy completed successfully.',
            'status' => 'completed',
            'created_at' => $lmp1->copy()->addWeeks(8),
            'updated_at' => $edd1->copy()->addDays(3),
        ]);

        // Second pregnancy (more recent)
        $lmp2 = Carbon::now()->subYears(rand(1, 3));
        $edd2 = $lmp2->copy()->addDays(280);

        PrenatalRecord::create([
            'patient_id' => $patient->id,
            'last_menstrual_period' => $lmp2,
            'expected_due_date' => $edd2,
            'gravida' => 2,
            'para' => 1,
            'medical_history' => 'Previous normal delivery, no complications',
            'notes' => 'Second pregnancy, routine care provided.',
            'status' => 'completed',
            'created_at' => $lmp2->copy()->addWeeks(6),
            'updated_at' => $edd2->copy()->addDays(5),
        ]);
    }

    private function createHighRiskPregnancy($patient)
    {
        $lmp = Carbon::now()->subWeeks(rand(12, 32)); // Current high-risk pregnancy
        $edd = $lmp->copy()->addDays(280);
        $currentWeeks = $lmp->diffInWeeks(Carbon::now());

        $highRiskConditions = [
            'Gestational diabetes, diet controlled',
            'Pregnancy-induced hypertension, monitoring required',
            'Previous cesarean section, VBAC candidate',
            'Advanced maternal age (>35), increased monitoring',
            'Multiple gestation (twins) detected',
            'Placenta previa, restricted activity recommended'
        ];

        PrenatalRecord::create([
            'patient_id' => $patient->id,
            'last_menstrual_period' => $lmp,
            'expected_due_date' => $edd,
            'gravida' => rand(2, 5),
            'para' => rand(1, 4),
            'medical_history' => $highRiskConditions[array_rand($highRiskConditions)],
            'notes' => 'High-risk pregnancy requiring frequent monitoring and specialized care.',
            'status' => 'high-risk',
            'created_at' => $lmp->copy()->addWeeks(4),
            'updated_at' => Carbon::now()->subDays(rand(1, 3)),
        ]);
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
            null,
            'No significant medical history',
            'Previous cesarean section',
            'History of gestational diabetes',
            'Hypertension, controlled with medication',
            'Previous miscarriage',
            'Anemia, taking iron supplements',
            'No known allergies or medical conditions',
            'History of preterm labor',
            'Thyroid condition, under monitoring',
            'Asthma, controlled with inhaler',
            'Family history of diabetes'
        ];

        return $histories[array_rand($histories)];
    }

    private function getRealisticGravida($age)
    {
        // Gravida (total pregnancies) based on age
        if ($age < 20) return rand(1, 2);
        if ($age < 25) return rand(1, 3);
        if ($age < 30) return rand(1, 4);
        if ($age < 35) return rand(2, 5);
        return rand(2, 7); // Older mothers may have more pregnancies
    }

    private function getRealisticPara($age)
    {
        // Para (live births) should be less than or equal to gravida
        $gravida = $this->getRealisticGravida($age);
        $para = rand(0, max(1, $gravida - 1));
        return min($para, $gravida); // Para cannot exceed gravida
    }
}