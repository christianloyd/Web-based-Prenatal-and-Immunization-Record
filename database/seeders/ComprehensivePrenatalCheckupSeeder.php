<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PrenatalRecord;
use App\Models\PrenatalCheckup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ComprehensivePrenatalCheckupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get patients with active prenatal records
        $patients = Patient::all()->keyBy(function($patient) {
            return $patient->first_name . ' ' . $patient->last_name;
        });

        // Get prenatal records for active pregnancies
        $prenatalRecords = PrenatalRecord::whereNull('delivery_date')->get()->keyBy('patient_id');

        $checkups = [
            // Maria Santos - Early pregnancy checkups
            [
                'patient_id' => $patients['Maria Santos']->id,
                'prenatal_record_id' => $prenatalRecords[$patients['Maria Santos']->id]->id ?? null,
                'checkup_date' => Carbon::now()->subDays(35),
                'gestational_age' => '12 weeks 3 days',
                'weight' => 59.0,
                'blood_pressure' => '118/75',
                'fundal_height' => null, // Too early
                'fetal_heart_rate' => 150,
                'presentation' => null, // Too early
                'complaints' => 'Mild morning sickness',
                'findings' => 'Normal fetal heartbeat detected. Uterus size appropriate for gestational age.',
                'recommendations' => 'Continue folic acid supplements. Return in 4 weeks. Avoid smoking and alcohol.',
                'next_appointment' => Carbon::now()->subDays(7),
                'attended_by' => 'Midwife Ana Rodriguez',
            ],
            [
                'patient_id' => $patients['Maria Santos']->id,
                'prenatal_record_id' => $prenatalRecords[$patients['Maria Santos']->id]->id ?? null,
                'checkup_date' => Carbon::now()->subDays(7),
                'gestational_age' => '16 weeks 3 days',
                'weight' => 60.2,
                'blood_pressure' => '120/78',
                'fundal_height' => 16.0,
                'fetal_heart_rate' => 155,
                'presentation' => null, // Still early
                'complaints' => 'Morning sickness improved',
                'findings' => 'Good fetal movement felt. Fundal height appropriate. Weight gain within normal range.',
                'recommendations' => 'Start iron supplements. Schedule ultrasound at 20 weeks.',
                'next_appointment' => Carbon::now()->addDays(21),
                'attended_by' => 'Midwife Ana Rodriguez',
            ],

            // Ana Cruz - Mid pregnancy checkups
            [
                'patient_id' => $patients['Ana Cruz']->id,
                'prenatal_record_id' => $prenatalRecords[$patients['Ana Cruz']->id]->id ?? null,
                'checkup_date' => Carbon::now()->subDays(45),
                'gestational_age' => '20 weeks 2 days',
                'weight' => 54.5,
                'blood_pressure' => '115/72',
                'fundal_height' => 20.0,
                'fetal_heart_rate' => 145,
                'presentation' => null, // Too early to determine
                'complaints' => 'Feeling baby movements',
                'findings' => 'Ultrasound shows normal fetal development. Anatomy scan normal.',
                'recommendations' => 'Continue prenatal vitamins. Increase protein intake.',
                'next_appointment' => Carbon::now()->subDays(17),
                'attended_by' => 'Midwife Ana Rodriguez',
            ],
            [
                'patient_id' => $patients['Ana Cruz']->id,
                'prenatal_record_id' => $prenatalRecords[$patients['Ana Cruz']->id]->id ?? null,
                'checkup_date' => Carbon::now()->subDays(17),
                'gestational_age' => '24 weeks 0 days',
                'weight' => 56.0,
                'blood_pressure' => '118/75',
                'fundal_height' => 24.0,
                'fetal_heart_rate' => 150,
                'presentation' => 'Vertex (head down)',
                'complaints' => 'Occasional back pain',
                'findings' => 'Baby in good position. Growth appropriate for gestational age.',
                'recommendations' => 'Back exercises. Prenatal yoga recommended.',
                'next_appointment' => Carbon::now()->addDays(11),
                'attended_by' => 'Midwife Ana Rodriguez',
            ],

            // Rosa Garcia - High-risk pregnancy, frequent checkups
            [
                'patient_id' => $patients['Rosa Garcia']->id,
                'prenatal_record_id' => $prenatalRecords[$patients['Rosa Garcia']->id]->id ?? null,
                'checkup_date' => Carbon::now()->subDays(30),
                'gestational_age' => '22 weeks 1 day',
                'weight' => 70.0,
                'blood_pressure' => '145/95',
                'fundal_height' => 22.0,
                'fetal_heart_rate' => 148,
                'presentation' => 'Vertex',
                'complaints' => 'Headaches, swelling in feet',
                'findings' => 'Elevated blood pressure. Mild edema in lower extremities. Fetal growth normal.',
                'recommendations' => 'Increase hypertension medication. Weekly BP monitoring. Reduce salt intake.',
                'next_appointment' => Carbon::now()->subDays(23),
                'attended_by' => 'Dr. Maria Santos (referred)',
            ],
            [
                'patient_id' => $patients['Rosa Garcia']->id,
                'prenatal_record_id' => $prenatalRecords[$patients['Rosa Garcia']->id]->id ?? null,
                'checkup_date' => Carbon::now()->subDays(23),
                'gestational_age' => '23 weeks 0 days',
                'weight' => 71.2,
                'blood_pressure' => '142/92',
                'fundal_height' => 23.0,
                'fetal_heart_rate' => 152,
                'presentation' => 'Vertex',
                'complaints' => 'Headaches persisting',
                'findings' => 'BP still elevated but improved. Protein in urine trace positive.',
                'recommendations' => 'Continue medication. Rest more. Weekly appointments.',
                'next_appointment' => Carbon::now()->subDays(16),
                'attended_by' => 'Dr. Maria Santos',
            ],
            [
                'patient_id' => $patients['Rosa Garcia']->id,
                'prenatal_record_id' => $prenatalRecords[$patients['Rosa Garcia']->id]->id ?? null,
                'checkup_date' => Carbon::now()->subDays(16),
                'gestational_age' => '24 weeks 0 days',
                'weight' => 71.8,
                'blood_pressure' => '138/88',
                'fundal_height' => 24.0,
                'fetal_heart_rate' => 155,
                'presentation' => 'Vertex',
                'complaints' => 'Less headaches',
                'findings' => 'BP improving with medication. Fetal movement good. No protein in urine.',
                'recommendations' => 'Continue current treatment. Bi-weekly checkups.',
                'next_appointment' => Carbon::now()->subDays(2),
                'attended_by' => 'Dr. Maria Santos',
            ],

            // Luz Reyes - Near term pregnancy
            [
                'patient_id' => $patients['Luz Reyes']->id,
                'prenatal_record_id' => $prenatalRecords[$patients['Luz Reyes']->id]->id ?? null,
                'checkup_date' => Carbon::now()->subDays(14),
                'gestational_age' => '36 weeks 2 days',
                'weight' => 68.0,
                'blood_pressure' => '128/82',
                'fundal_height' => 36.0,
                'fetal_heart_rate' => 142,
                'presentation' => 'Vertex (engaged)',
                'complaints' => 'Braxton Hicks contractions, pelvic pressure',
                'findings' => 'Baby engaged in pelvis. Cervix still closed. Good fetal movement.',
                'recommendations' => 'Watch for signs of labor. Hospital bag ready. Weekly visits.',
                'next_appointment' => Carbon::now()->subDays(7),
                'attended_by' => 'Midwife Ana Rodriguez',
            ],
            [
                'patient_id' => $patients['Luz Reyes']->id,
                'prenatal_record_id' => $prenatalRecords[$patients['Luz Reyes']->id]->id ?? null,
                'checkup_date' => Carbon::now()->subDays(7),
                'gestational_age' => '37 weeks 2 days',
                'weight' => 68.5,
                'blood_pressure' => '125/80',
                'fundal_height' => 37.0,
                'fetal_heart_rate' => 140,
                'presentation' => 'Vertex (fully engaged)',
                'complaints' => 'More frequent contractions, back pain',
                'findings' => 'Cervix 1cm dilated, 50% effaced. Baby ready for delivery.',
                'recommendations' => 'Come to center when contractions are 5 minutes apart. Call immediately for any bleeding or water breaking.',
                'next_appointment' => Carbon::now(),
                'attended_by' => 'Midwife Ana Rodriguez',
            ],

            // Elena Torres - Third pregnancy, experienced mother
            [
                'patient_id' => $patients['Elena Torres']->id,
                'prenatal_record_id' => $prenatalRecords[$patients['Elena Torres']->id]->id ?? null,
                'checkup_date' => Carbon::now()->subDays(21),
                'gestational_age' => '10 weeks 1 day',
                'weight' => 62.5,
                'blood_pressure' => '120/78',
                'fundal_height' => null, // Too early
                'fetal_heart_rate' => 165,
                'presentation' => null,
                'complaints' => 'Mild nausea, fatigue',
                'findings' => 'Strong fetal heartbeat. Uterus appropriate size. No complications.',
                'recommendations' => 'Continue folic acid. RhoGAM injection scheduled at 28 weeks.',
                'next_appointment' => Carbon::now()->addDays(7),
                'attended_by' => 'Midwife Ana Rodriguez',
            ],
        ];

        foreach ($checkups as $checkupData) {
            if ($checkupData['prenatal_record_id']) {
                PrenatalCheckup::create($checkupData);
            }
        }
    }
}