<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrenatalRecord;
use App\Models\PrenatalCheckup;
use App\Models\User;
use Carbon\Carbon;

class PrenatalCheckupSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Creating prenatal checkups following realistic progression...');

        // Get all prenatal records
        $prenatalRecords = PrenatalRecord::with('patient')->get();

        if ($prenatalRecords->isEmpty()) {
            $this->command->error('No prenatal records found! Please run PrenatalRecordSeeder first.');
            return;
        }

        // Get available healthcare workers
        $healthcareWorkers = User::whereIn('role', ['midwife', 'bhw'])->pluck('id')->toArray();

        if (empty($healthcareWorkers)) {
            $this->command->error('No healthcare workers found! Please ensure users with midwife/bhw roles exist.');
            return;
        }

        $totalCheckups = 0;
        $upcomingCheckups = 0;

        foreach ($prenatalRecords as $record) {
            $this->command->info("Generating checkups for patient: {$record->patient->name}");

            // Determine what type of checkup series to create based on pregnancy status
            switch ($record->status) {
                case 'completed':
                    $checkupCount = $this->createCompletedPregnancyCheckups($record, $healthcareWorkers);
                    break;
                case 'normal':
                case 'monitor':
                case 'due':
                case 'high-risk':
                    $checkupCount = $this->createOngoingPregnancyCheckups($record, $healthcareWorkers);
                    break;
                default:
                    $checkupCount = $this->createBasicCheckups($record, $healthcareWorkers);
            }

            $totalCheckups += $checkupCount['done'];
            $upcomingCheckups += $checkupCount['upcoming'];
        }

        $this->command->info('Prenatal Checkup seeder completed successfully!');
        $this->command->info("Generated: {$totalCheckups} completed checkups and {$upcomingCheckups} upcoming checkups");
    }

    private function createCompletedPregnancyCheckups($record, $healthcareWorkers)
    {
        $lmp = Carbon::parse($record->last_menstrual_period);
        $edd = Carbon::parse($record->expected_due_date);

        // Standard checkup schedule for completed pregnancy
        $checkupSchedule = [
            ['week' => 8, 'description' => 'First prenatal visit'],
            ['week' => 12, 'description' => 'First trimester screening'],
            ['week' => 16, 'description' => 'Second trimester checkup'],
            ['week' => 20, 'description' => 'Anatomy scan'],
            ['week' => 24, 'description' => 'Glucose screening'],
            ['week' => 28, 'description' => 'Third trimester begins'],
            ['week' => 32, 'description' => 'Regular monitoring'],
            ['week' => 36, 'description' => 'Pre-delivery preparation'],
            ['week' => 39, 'description' => 'Final checkup'],
        ];

        $createdCheckups = 0;
        foreach ($checkupSchedule as $schedule) {
            $checkupDate = $lmp->copy()->addWeeks($schedule['week']);

            // Only create checkups that would have occurred before delivery
            if ($checkupDate->lte($edd)) {
                PrenatalCheckup::create([
                    'patient_id' => $record->patient_id,
                    'prenatal_record_id' => $record->id,
                    'checkup_date' => $checkupDate,
                    'checkup_time' => '09:' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT),
                    'gestational_age_weeks' => $schedule['week'],
                    'weight_kg' => $this->calculateProgressiveWeight($schedule['week']),
                    'blood_pressure_systolic' => $this->getRealisticSystolic($schedule['week']),
                    'blood_pressure_diastolic' => $this->getRealisticDiastolic($schedule['week']),
                    'fundal_height_cm' => max(0, $schedule['week'] - 4) + rand(-1, 2),
                    'fetal_heart_rate' => rand(120, 160),
                    'baby_movement' => $schedule['week'] > 16 ? ['active', 'normal'][rand(0, 1)] : null,
                    'symptoms' => $this->getRandomSymptoms(),
                    'notes' => $schedule['description'] . '. ' . $this->getRandomCheckupNotes(),
                    'next_visit_date' => $this->getNextVisitDate($checkupDate, $schedule['week']),
                    'status' => 'done',
                    'conducted_by' => $healthcareWorkers[array_rand($healthcareWorkers)],
                    'created_at' => $checkupDate,
                    'updated_at' => $checkupDate,
                ]);
                $createdCheckups++;
            }
        }

        return ['done' => $createdCheckups, 'upcoming' => 0];
    }

    private function createOngoingPregnancyCheckups($record, $healthcareWorkers)
    {
        $lmp = Carbon::parse($record->last_menstrual_period);
        $currentWeeks = $lmp->diffInWeeks(Carbon::now());

        // Create checkups that have already occurred
        $checkupWeeks = [8, 12, 16, 20, 24, 28, 32, 36];
        $doneCheckups = 0;
        $upcomingCheckups = 0;

        foreach ($checkupWeeks as $week) {
            $checkupDate = $lmp->copy()->addWeeks($week);

            if ($week <= $currentWeeks) {
                // Past checkups (done)
                PrenatalCheckup::create([
                    'patient_id' => $record->patient_id,
                    'prenatal_record_id' => $record->id,
                    'checkup_date' => $checkupDate,
                    'checkup_time' => '09:' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT),
                    'gestational_age_weeks' => $week,
                    'weight_kg' => $this->calculateProgressiveWeight($week),
                    'blood_pressure_systolic' => $this->getRealisticSystolic($week),
                    'blood_pressure_diastolic' => $this->getRealisticDiastolic($week),
                    'fundal_height_cm' => max(0, $week - 4) + rand(-1, 2),
                    'fetal_heart_rate' => rand(120, 160),
                    'baby_movement' => $week > 16 ? ['active', 'normal', 'less'][rand(0, 2)] : null,
                    'symptoms' => $this->getRandomSymptoms(),
                    'notes' => $this->getRandomCheckupNotes(),
                    'next_visit_date' => $this->getNextVisitDate($checkupDate, $week),
                    'status' => 'done',
                    'conducted_by' => $healthcareWorkers[array_rand($healthcareWorkers)],
                    'created_at' => $checkupDate,
                    'updated_at' => $checkupDate,
                ]);
                $doneCheckups++;
            } elseif ($week <= $currentWeeks + 8) {
                // Future checkups (upcoming) - only schedule within next 8 weeks
                PrenatalCheckup::create([
                    'patient_id' => $record->patient_id,
                    'prenatal_record_id' => $record->id,
                    'checkup_date' => $checkupDate,
                    'checkup_time' => '09:' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT),
                    'gestational_age_weeks' => $week,
                    'status' => 'upcoming',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                $upcomingCheckups++;
            }
        }

        return ['done' => $doneCheckups, 'upcoming' => $upcomingCheckups];
    }

    private function createBasicCheckups($record, $healthcareWorkers)
    {
        // For records without specific status, create 2-3 basic checkups
        $lmp = Carbon::parse($record->last_menstrual_period);
        $checkupCount = rand(2, 3);
        $createdCheckups = 0;

        for ($i = 0; $i < $checkupCount; $i++) {
            $weeks = 12 + ($i * 8); // Checkups at 12, 20, 28 weeks
            $checkupDate = $lmp->copy()->addWeeks($weeks);

            if ($checkupDate->isPast()) {
                PrenatalCheckup::create([
                    'patient_id' => $record->patient_id,
                    'prenatal_record_id' => $record->id,
                    'checkup_date' => $checkupDate,
                    'checkup_time' => '09:' . str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT),
                    'gestational_age_weeks' => $weeks,
                    'weight_kg' => $this->calculateProgressiveWeight($weeks),
                    'blood_pressure_systolic' => rand(110, 135),
                    'blood_pressure_diastolic' => rand(70, 85),
                    'fundal_height_cm' => max(0, $weeks - 4),
                    'fetal_heart_rate' => rand(120, 160),
                    'baby_movement' => $weeks > 16 ? 'normal' : null,
                    'symptoms' => $this->getRandomSymptoms(),
                    'notes' => $this->getRandomCheckupNotes(),
                    'status' => 'done',
                    'conducted_by' => $healthcareWorkers[array_rand($healthcareWorkers)],
                    'created_at' => $checkupDate,
                    'updated_at' => $checkupDate,
                ]);
                $createdCheckups++;
            }
        }

        return ['done' => $createdCheckups, 'upcoming' => 0];
    }

    private function calculateProgressiveWeight($gestationalWeek)
    {
        // More realistic Filipino maternal weight progression
        $baseWeight = rand(4500, 7500) / 100; // 45.00 to 75.00 kg (Filipino average)

        // Realistic weight gain by trimester
        if ($gestationalWeek <= 12) {
            // First trimester: minimal gain or even loss due to nausea
            $weightGain = rand(-200, 200) / 100; // -2kg to +2kg
        } elseif ($gestationalWeek <= 27) {
            // Second trimester: steady gain 0.3-0.5kg per week
            $weeksSince12 = $gestationalWeek - 12;
            $weightGain = $weeksSince12 * (rand(25, 50) / 100);
        } else {
            // Third trimester: continued gain but may slow near term
            $secondTrimesterGain = 15 * 0.375; // ~5.6kg from second trimester
            $weeksSince28 = $gestationalWeek - 28;
            $thirdTrimesterGain = $weeksSince28 * (rand(30, 45) / 100);
            $weightGain = $secondTrimesterGain + $thirdTrimesterGain;
        }

        return round($baseWeight + $weightGain, 1);
    }

    private function getNextVisitDate($currentDate, $weeks)
    {
        if ($weeks < 28) return $currentDate->copy()->addWeeks(4);
        if ($weeks < 36) return $currentDate->copy()->addWeeks(2);
        return $currentDate->copy()->addWeeks(1);
    }

    private function getRandomCheckupNotes()
    {
        $notes = [
            'Normal development, all vitals stable',
            'Baby developing well, mother feeling good',
            'Excellent fetal heart rate, active baby movements',
            'Weight gain within normal range',
            'All tests normal, continue current care plan',
            'Baby position optimal for development',
            'Fundal height appropriate for gestational age',
            'No complications noted, routine follow-up',
            'Patient reports good fetal movements',
            'Urine test normal, no protein detected',
            'Blood pressure within normal limits',
            'Patient education provided on nutrition',
            'Cervix remains closed and long',
            'Recommended prenatal vitamins',
            'Patient counseled on warning signs'
        ];

        return $notes[array_rand($notes)];
    }

    private function getRandomSymptoms()
    {
        $symptoms = [
            'No symptoms reported',
            'Mild nausea in mornings',
            'Occasional heartburn',
            'Some back pain',
            'Mild fatigue',
            'Normal pregnancy discomfort',
            'Good energy levels',
            'Sleeping well',
            'Active baby movements felt',
            'Slight ankle swelling'
        ];

        return $symptoms[array_rand($symptoms)];
    }

    private function getRealisticSystolic($gestationalWeek)
    {
        // Blood pressure changes throughout pregnancy
        if ($gestationalWeek <= 20) {
            // Early pregnancy: slight decrease
            return rand(100, 125);
        } elseif ($gestationalWeek <= 32) {
            // Mid pregnancy: gradual return to baseline
            return rand(105, 130);
        } else {
            // Late pregnancy: may increase slightly
            return rand(110, 135);
        }
    }

    private function getRealisticDiastolic($gestationalWeek)
    {
        // Diastolic typically decreases more in early pregnancy
        if ($gestationalWeek <= 20) {
            return rand(60, 80);
        } elseif ($gestationalWeek <= 32) {
            return rand(65, 85);
        } else {
            return rand(70, 90);
        }
    }
}