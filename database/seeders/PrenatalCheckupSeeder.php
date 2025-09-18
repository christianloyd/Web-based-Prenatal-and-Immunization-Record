<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrenatalCheckupSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        DB::table('prenatal_checkups')->delete();

        // Get active prenatal records
        $activePrenatalRecords = DB::table('prenatal_records')
            ->where('is_active', true)
            ->get(['id', 'patient_id'])
            ->toArray();

        if (empty($activePrenatalRecords)) {
            $this->command->error('No active prenatal records found! Please run PrenatalRecordSeeder first.');
            return;
        }

        // Generate prenatal checkups
        $checkups = [];
        $checkupCounter = 1;

        // Generate completed checkups for active prenatal records
        foreach ($activePrenatalRecords as $record) {
            // Generate 2-4 completed checkups per active prenatal record
            $numCompletedCheckups = rand(2, 4);
            
            for ($j = 0; $j < $numCompletedCheckups; $j++) {
                $checkupDate = Carbon::now()->subDays(rand(7, 120));
                
                $checkups[] = [
                    'formatted_checkup_id' => 'PC' . str_pad($checkupCounter++, 4, '0', STR_PAD_LEFT),
                    'prenatal_record_id' => $record->id,
                    'patient_id' => $record->patient_id,
                    'checkup_date' => $checkupDate->format('Y-m-d'),
                    'checkup_time' => $checkupDate->format('H:i:s'),
                    'weeks_pregnant' => rand(8, 36) . ' weeks',
                    'gestational_age_weeks' => rand(8, 36),
                    'bp_high' => rand(110, 140),
                    'bp_low' => rand(60, 90),
                    'blood_pressure_systolic' => rand(110, 140),
                    'blood_pressure_diastolic' => rand(60, 90),
                    'weight' => rand(45, 85) + (rand(0, 99) / 100),
                    'weight_kg' => rand(45, 85) + (rand(0, 99) / 100),
                    'baby_heartbeat' => rand(120, 160),
                    'fetal_heart_rate' => rand(120, 160),
                    'belly_size' => rand(20, 40) + (rand(0, 99) / 100),
                    'fundal_height_cm' => rand(20, 40) + (rand(0, 99) / 100),
                    'presentation' => ['Vertex', 'Breech', 'Transverse'][array_rand(['Vertex', 'Breech', 'Transverse'])],
                    'baby_movement' => ['active', 'normal', 'less'][array_rand(['active', 'normal', 'less'])],
                    'swelling' => json_encode([
                        'feet' => rand(0, 1) ? 'mild' : 'none',
                        'hands' => rand(0, 1) ? 'mild' : 'none',
                        'face' => rand(0, 1) ? 'mild' : 'none'
                    ]),
                    'symptoms' => 'Normal pregnancy symptoms',
                    'notes' => $this->getRandomCheckupNotes(),
                    'conducted_by' => rand(1, 4), // Assuming user IDs 1-4 exist
                    'next_visit_date' => null,
                    'next_visit_time' => null,
                    'next_visit_notes' => null,
                    'status' => 'done',
                    'created_at' => $checkupDate,
                    'updated_at' => $checkupDate
                ];
            }
        }

        // Generate 10 upcoming/scheduled checkups
        // Select 10 random patients from active prenatal records
        $upcomingPatients = collect($activePrenatalRecords)->random(min(10, count($activePrenatalRecords)));
        
        foreach ($upcomingPatients as $record) {
            $nextVisitDate = Carbon::now()->addDays(rand(1, 30));
            
            $checkups[] = [
                'formatted_checkup_id' => 'PC' . str_pad($checkupCounter++, 4, '0', STR_PAD_LEFT),
                'prenatal_record_id' => $record->id,
                'patient_id' => $record->patient_id,
                'checkup_date' => $nextVisitDate->format('Y-m-d'),
                'checkup_time' => $nextVisitDate->setTime(rand(8, 16), [0, 30][rand(0, 1)])->format('H:i:s'),
                'weeks_pregnant' => null,
                'gestational_age_weeks' => null,
                'bp_high' => null,
                'bp_low' => null,
                'blood_pressure_systolic' => null,
                'blood_pressure_diastolic' => null,
                'weight' => null,
                'weight_kg' => null,
                'baby_heartbeat' => null,
                'fetal_heart_rate' => null,
                'belly_size' => null,
                'fundal_height_cm' => null,
                'presentation' => null,
                'baby_movement' => null,
                'swelling' => null,
                'symptoms' => null,
                'notes' => 'Scheduled checkup',
                'conducted_by' => null,
                'next_visit_date' => $nextVisitDate->format('Y-m-d'),
                'next_visit_time' => $nextVisitDate->format('H:i:s'),
                'next_visit_notes' => 'Regular prenatal checkup',
                'status' => 'upcoming',
                'created_at' => Carbon::now()->subDays(rand(1, 14)),
                'updated_at' => Carbon::now()->subDays(rand(1, 7))
            ];
        }

        // Insert checkups
        DB::table('prenatal_checkups')->insert($checkups);

        $completedCheckups = collect($checkups)->where('status', 'completed')->count();
        $upcomingCheckups = collect($checkups)->where('status', 'upcoming')->count();

        $this->command->info('Prenatal Checkup seeder completed successfully!');
        $this->command->info("Generated: {$completedCheckups} completed checkups and {$upcomingCheckups} upcoming checkups");
    }

    private function getRandomCheckupNotes()
    {
        $notes = [
            'Normal development, all vitals stable',
            'Baby developing well, mother feeling good',
            'Slight swelling noted, advised to elevate feet',
            'Blood pressure slightly elevated, monitoring needed',
            'Excellent fetal heart rate, active baby movements',
            'Weight gain within normal range',
            'Mild morning sickness, prescribed vitamins',
            'All tests normal, continue current care plan',
            'Baby position optimal for delivery',
            'Fundal height appropriate for gestational age',
            'No complications noted, routine follow-up',
            'Patient reports good fetal movements',
            'Urine test normal, no protein detected',
            'Blood sugar levels within normal limits',
            'Patient education provided on nutrition'
        ];

        return $notes[array_rand($notes)];
    }
}