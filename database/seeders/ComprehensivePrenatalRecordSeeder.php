<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PrenatalRecord;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ComprehensivePrenatalRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get patients by name for specific records
        $patients = Patient::all()->keyBy(function($patient) {
            return $patient->first_name . ' ' . $patient->last_name;
        });

        $prenatalRecords = [
            // Active pregnancy - Maria Santos (Early pregnancy)
            [
                'patient_id' => $patients['Maria Santos']->id,
                'pregnancy_number' => 2,
                'last_menstrual_period' => Carbon::parse('2024-06-15'),
                'expected_delivery_date' => Carbon::parse('2025-03-22'),
                'blood_type' => 'O+',
                'weight' => 58.5,
                'height' => 162.0,
                'blood_pressure' => '120/80',
                'risk_category' => 'low',
                'notes' => 'Regular prenatal visits, taking folic acid supplements. Second pregnancy progressing well.',
                'created_at' => Carbon::now()->subDays(45),
                'updated_at' => Carbon::now()->subDays(10),
            ],
            // Active pregnancy - Ana Cruz (Mid pregnancy)
            [
                'patient_id' => $patients['Ana Cruz']->id,
                'pregnancy_number' => 1,
                'last_menstrual_period' => Carbon::parse('2024-04-10'),
                'expected_delivery_date' => Carbon::parse('2025-01-15'),
                'blood_type' => 'A+',
                'weight' => 52.0,
                'height' => 158.0,
                'blood_pressure' => '115/75',
                'risk_category' => 'low',
                'notes' => 'First-time mother, very compliant with prenatal care. All screenings normal.',
                'created_at' => Carbon::now()->subDays(75),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            // High-risk pregnancy - Rosa Garcia
            [
                'patient_id' => $patients['Rosa Garcia']->id,
                'pregnancy_number' => 3,
                'last_menstrual_period' => Carbon::parse('2024-05-20'),
                'expected_delivery_date' => Carbon::parse('2025-02-24'),
                'blood_type' => 'B+',
                'weight' => 68.0,
                'height' => 155.0,
                'blood_pressure' => '140/90',
                'risk_category' => 'high',
                'notes' => 'High blood pressure requires close monitoring. Weekly visits recommended. On medication for hypertension.',
                'created_at' => Carbon::now()->subDays(60),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            // Late pregnancy - Luz Reyes
            [
                'patient_id' => $patients['Luz Reyes']->id,
                'pregnancy_number' => 2,
                'last_menstrual_period' => Carbon::parse('2024-02-28'),
                'expected_delivery_date' => Carbon::parse('2024-12-05'),
                'blood_type' => 'AB+',
                'weight' => 65.5,
                'height' => 160.0,
                'blood_pressure' => '125/85',
                'risk_category' => 'moderate',
                'notes' => 'Previous C-section, planning for VBAC. Baby position normal. Ready for delivery soon.',
                'created_at' => Carbon::now()->subDays(120),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            // Active pregnancy - Elena Torres (Experienced mother)
            [
                'patient_id' => $patients['Elena Torres']->id,
                'pregnancy_number' => 3,
                'last_menstrual_period' => Carbon::parse('2024-07-01'),
                'expected_delivery_date' => Carbon::parse('2025-04-07'),
                'blood_type' => 'O-',
                'weight' => 62.0,
                'height' => 164.0,
                'blood_pressure' => '118/78',
                'risk_category' => 'low',
                'notes' => 'Third pregnancy, experienced mother. Two previous normal deliveries. Rh-negative, receiving RhoGAM.',
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(7),
            ],
            // Completed pregnancy - Carmen Mendoza (Recently delivered)
            [
                'patient_id' => $patients['Carmen Mendoza']->id,
                'pregnancy_number' => 1,
                'last_menstrual_period' => Carbon::parse('2023-10-15'),
                'expected_delivery_date' => Carbon::parse('2024-07-20'),
                'blood_type' => 'A+',
                'weight' => 55.0,
                'height' => 159.0,
                'blood_pressure' => '120/80',
                'risk_category' => 'low',
                'notes' => 'COMPLETED: Normal vaginal delivery on July 22, 2024. Healthy baby girl, 3.2 kg. No complications. Currently in postpartum care.',
                'delivery_date' => Carbon::parse('2024-07-22'),
                'delivery_type' => 'normal_delivery',
                'baby_weight' => 3200,
                'baby_gender' => 'female',
                'delivery_complications' => null,
                'created_at' => Carbon::now()->subDays(200),
                'updated_at' => Carbon::now()->subDays(75),
            ],
            // Completed pregnancy - Isabel Villanueva (6 months ago)
            [
                'patient_id' => $patients['Isabel Villanueva']->id,
                'pregnancy_number' => 1,
                'last_menstrual_period' => Carbon::parse('2023-08-10'),
                'expected_delivery_date' => Carbon::parse('2024-05-15'),
                'blood_type' => 'O+',
                'weight' => 50.5,
                'height' => 156.0,
                'blood_pressure' => '115/70',
                'risk_category' => 'low',
                'notes' => 'COMPLETED: Normal delivery on May 18, 2024. Healthy baby boy, 3.4 kg. Breastfeeding well. Postpartum recovery excellent.',
                'delivery_date' => Carbon::parse('2024-05-18'),
                'delivery_type' => 'normal_delivery',
                'baby_weight' => 3400,
                'baby_gender' => 'male',
                'delivery_complications' => null,
                'created_at' => Carbon::now()->subDays(240),
                'updated_at' => Carbon::now()->subDays(120),
            ],
            // Completed pregnancy - Gloria Ramos (Twins delivery)
            [
                'patient_id' => $patients['Gloria Ramos']->id,
                'pregnancy_number' => 2,
                'last_menstrual_period' => Carbon::parse('2023-04-20'),
                'expected_delivery_date' => Carbon::parse('2024-01-25'),
                'blood_type' => 'B+',
                'weight' => 63.0,
                'height' => 161.0,
                'blood_pressure' => '130/85',
                'risk_category' => 'high',
                'notes' => 'COMPLETED: Twin pregnancy delivered via C-section on January 20, 2024. Twin A: 2.8 kg (female), Twin B: 2.6 kg (male). Both babies healthy.',
                'delivery_date' => Carbon::parse('2024-01-20'),
                'delivery_type' => 'cesarean',
                'baby_weight' => 2800, // Twin A weight
                'baby_gender' => 'twins',
                'delivery_complications' => 'Multiple gestation, planned C-section',
                'created_at' => Carbon::now()->subDays(300),
                'updated_at' => Carbon::now()->subDays(200),
            ],
        ];

        foreach ($prenatalRecords as $recordData) {
            PrenatalRecord::create($recordData);
        }
    }
}