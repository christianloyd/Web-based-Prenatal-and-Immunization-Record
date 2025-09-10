<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\PrenatalRecord;
use App\Models\PrenatalCheckup;
use App\Models\ChildRecord;
use App\Models\Immunization;
use Carbon\Carbon;

class DashboardSampleDataSeeder extends Seeder
{
    public function run()
    {
        // Create 10 sample patients
        for ($i = 1; $i <= 10; $i++) {
            $patient = Patient::create([
                'formatted_patient_id' => 'P' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => 'Patient ' . $i,
                'age' => rand(20, 40),
                'contact' => '0912345678' . $i,
                'emergency_contact' => '0987654321' . $i,
                'address' => 'Davao City, Address ' . $i,
                'occupation' => 'Teacher',
                'created_at' => Carbon::now()->subMonths(rand(1, 12))
            ]);
            
            // Create prenatal record for each patient
            $prenatalRecord = PrenatalRecord::create([
                'patient_id' => $patient->id,
                'last_menstrual_period' => Carbon::now()->subMonths(rand(6, 9)),
                'expected_due_date' => Carbon::now()->addMonths(rand(1, 4)),
                'gestational_age' => rand(20, 36) . ' weeks',
                'trimester' => rand(2, 3),
                'gravida' => rand(1, 3),
                'para' => rand(0, 2),
                'medical_history' => 'No major complications',
                'notes' => 'Normal pregnancy progress',
                'last_visit' => Carbon::now()->subDays(rand(7, 30)),
                'status' => ['normal', 'monitor', 'high-risk'][rand(0, 2)],
                'blood_pressure' => '120/80',
                'weight' => rand(55, 80),
                'height' => rand(150, 170),
                'is_active' => true
            ]);

            // Create 3-6 checkups for each patient (spread over last 6 months)
            $numCheckups = rand(3, 6);
            for ($j = 0; $j < $numCheckups; $j++) {
                PrenatalCheckup::create([
                    'patient_id' => $patient->id,
                    'prenatal_record_id' => $prenatalRecord->id,
                    'checkup_date' => Carbon::now()->subMonths(rand(0, 6))->subDays(rand(1, 28)),
                    'checkup_time' => '09:00:00',
                    'weeks_pregnant' => rand(16, 36) . ' weeks',
                    'bp_high' => rand(110, 140),
                    'bp_low' => rand(70, 90),
                    'weight' => rand(55, 80),
                    'baby_heartbeat' => rand(120, 160),
                    'belly_size' => rand(25, 40),
                    'baby_movement' => ['active', 'normal', 'less'][rand(0, 2)],
                    'notes' => 'Regular checkup completed',
                    'conducted_by' => 'Midwife ' . rand(1, 3),
                    'status' => 'completed'
                ]);
            }

            // Create some child records (for patients who already gave birth)
            if (rand(1, 3) == 1) { // 33% chance
                $childRecord = ChildRecord::create([
                    'child_name' => 'Child of ' . $patient->name,
                    'gender' => ['Male', 'Female'][rand(0, 1)],
                    'birth_height' => rand(45, 55),
                    'birth_weight' => rand(2.5, 4.0),
                    'birthdate' => Carbon::now()->subMonths(rand(1, 24)),
                    'birthplace' => 'Davao City Hospital',
                    'address' => $patient->address,
                    'father_name' => 'Father ' . $i,
                    'phone_number' => $patient->contact,
                    'mother_name' => $patient->name,
                    'mother_id' => $patient->id
                ]);

                // Create immunization records for some children
                $vaccines = ['BCG', 'Hepatitis B', 'DPT', 'Polio', 'MMR', 'Tetanus'];
                $numVaccines = rand(2, 6);
                for ($k = 0; $k < $numVaccines; $k++) {
                    Immunization::create([
                        'child_record_id' => $childRecord->id,
                        'vaccine_name' => $vaccines[$k],
                        'dose' => 'Dose ' . rand(1, 3),
                        'schedule_date' => Carbon::now()->subMonths(rand(1, 12)),
                        'schedule_time' => '10:00:00',
                        'status' => ['Done', 'Upcoming', 'Missed'][rand(0, 2)],
                        'notes' => 'Vaccination completed successfully'
                    ]);
                }
            }
        }

        echo "Sample data created successfully!\n";
        echo "- 10 Patients\n";
        echo "- 10 Prenatal Records\n";
        echo "- " . PrenatalCheckup::count() . " Prenatal Checkups\n";
        echo "- " . ChildRecord::count() . " Child Records\n";
        echo "- " . Immunization::count() . " Immunizations\n";
    }
}