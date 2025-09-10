<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\ChildRecord;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ComprehensiveChildRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get patients by name
        $patients = Patient::all()->keyBy(function($patient) {
            return $patient->first_name . ' ' . $patient->last_name;
        });

        $childRecords = [
            // Carmen Mendoza's baby (3 months old)
            [
                'patient_id' => $patients['Carmen Mendoza']->id,
                'child_name' => 'Baby Girl Mendoza',
                'date_of_birth' => Carbon::parse('2024-07-22'),
                'gender' => 'female',
                'birth_weight' => 3.20,
                'birth_length' => 50.0,
                'current_weight' => 5.8,
                'current_height' => 58.0,
                'place_of_birth' => 'Barangay Health Center, Quezon City',
                'birth_attendant' => 'Midwife Ana Rodriguez',
                'delivery_type' => 'Normal Vaginal Delivery',
                'apgar_score' => '9/10',
                'complications' => null,
                'feeding_type' => 'Exclusive Breastfeeding',
                'notes' => 'Healthy baby girl, growing well. Regular breastfeeding. Mother very attentive to care.',
                'created_at' => Carbon::parse('2024-07-22'),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            // Isabel Villanueva's baby (6 months old)
            [
                'patient_id' => $patients['Isabel Villanueva']->id,
                'child_name' => 'Baby Boy Villanueva',
                'date_of_birth' => Carbon::parse('2024-05-18'),
                'gender' => 'male',
                'birth_weight' => 3.40,
                'birth_length' => 52.0,
                'current_weight' => 7.5,
                'current_height' => 65.0,
                'place_of_birth' => 'Barangay Health Center, Quezon City',
                'birth_attendant' => 'Midwife Ana Rodriguez',
                'delivery_type' => 'Normal Vaginal Delivery',
                'apgar_score' => '10/10',
                'complications' => null,
                'feeding_type' => 'Mixed Feeding',
                'notes' => 'Healthy baby boy, started complementary feeding at 6 months. Good weight gain. All developmental milestones met.',
                'created_at' => Carbon::parse('2024-05-18'),
                'updated_at' => Carbon::now()->subDays(10),
            ],
            // Gloria Ramos's twins (10 months old)
            [
                'patient_id' => $patients['Gloria Ramos']->id,
                'child_name' => 'Baby Girl Ramos (Twin A)',
                'date_of_birth' => Carbon::parse('2024-01-20'),
                'gender' => 'female',
                'birth_weight' => 2.80,
                'birth_length' => 48.0,
                'current_weight' => 8.2,
                'current_height' => 70.0,
                'place_of_birth' => 'District Hospital, Makati',
                'birth_attendant' => 'Dr. Maria Santos, OB-GYN',
                'delivery_type' => 'Cesarean Section',
                'apgar_score' => '8/9',
                'complications' => 'Premature birth (36 weeks), required NICU care for 1 week',
                'feeding_type' => 'Formula Feeding',
                'notes' => 'Twin A, born premature but caught up well. Currently eating solid foods. Active and healthy.',
                'created_at' => Carbon::parse('2024-01-20'),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            [
                'patient_id' => $patients['Gloria Ramos']->id,
                'child_name' => 'Baby Boy Ramos (Twin B)',
                'date_of_birth' => Carbon::parse('2024-01-20'),
                'gender' => 'male',
                'birth_weight' => 2.60,
                'birth_length' => 47.0,
                'current_weight' => 7.9,
                'current_height' => 69.0,
                'place_of_birth' => 'District Hospital, Makati',
                'birth_attendant' => 'Dr. Maria Santos, OB-GYN',
                'delivery_type' => 'Cesarean Section',
                'apgar_score' => '7/9',
                'complications' => 'Premature birth (36 weeks), required NICU care for 2 weeks, respiratory support',
                'feeding_type' => 'Formula Feeding',
                'notes' => 'Twin B, smaller twin, required longer NICU stay. Now thriving, good appetite. Slight developmental delay but catching up.',
                'created_at' => Carbon::parse('2024-01-20'),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            // Additional children for other patients (previous pregnancies)
            [
                'patient_id' => $patients['Maria Santos']->id,
                'child_name' => 'Sofia Santos',
                'date_of_birth' => Carbon::parse('2022-08-15'),
                'gender' => 'female',
                'birth_weight' => 3.10,
                'birth_length' => 49.0,
                'current_weight' => 12.5,
                'current_height' => 85.0,
                'place_of_birth' => 'Lying-in Clinic, Quezon City',
                'birth_attendant' => 'Midwife Rosa Cruz',
                'delivery_type' => 'Normal Vaginal Delivery',
                'apgar_score' => '9/10',
                'complications' => null,
                'feeding_type' => 'Weaned',
                'notes' => 'First child, now 2 years old. Very active toddler. Up to date with all immunizations. Speaks in short sentences.',
                'created_at' => Carbon::parse('2022-08-15'),
                'updated_at' => Carbon::now()->subDays(30),
            ],
            [
                'patient_id' => $patients['Rosa Garcia']->id,
                'child_name' => 'Miguel Garcia',
                'date_of_birth' => Carbon::parse('2021-03-10'),
                'gender' => 'male',
                'birth_weight' => 3.50,
                'birth_length' => 51.0,
                'current_weight' => 15.8,
                'current_height' => 95.0,
                'place_of_birth' => 'Barangay Health Center, Pasig',
                'birth_attendant' => 'Midwife Ana Rodriguez',
                'delivery_type' => 'Normal Vaginal Delivery',
                'apgar_score' => '10/10',
                'complications' => null,
                'feeding_type' => 'Weaned',
                'notes' => 'Healthy 3-year-old boy. Very energetic. Completed all immunizations for his age. Ready for pre-school.',
                'created_at' => Carbon::parse('2021-03-10'),
                'updated_at' => Carbon::now()->subDays(45),
            ],
            [
                'patient_id' => $patients['Rosa Garcia']->id,
                'child_name' => 'Carmen Garcia',
                'date_of_birth' => Carbon::parse('2019-11-22'),
                'gender' => 'female',
                'birth_weight' => 3.20,
                'birth_length' => 50.0,
                'current_weight' => 18.5,
                'current_height' => 105.0,
                'place_of_birth' => 'Barangay Health Center, Pasig',
                'birth_attendant' => 'Midwife Ana Rodriguez',
                'delivery_type' => 'Normal Vaginal Delivery',
                'apgar_score' => '9/10',
                'complications' => null,
                'feeding_type' => 'Weaned',
                'notes' => '5-year-old girl. In kindergarten. Very bright and social. All immunizations complete. No health issues.',
                'created_at' => Carbon::parse('2019-11-22'),
                'updated_at' => Carbon::now()->subDays(60),
            ],
        ];

        foreach ($childRecords as $childData) {
            ChildRecord::create($childData);
        }
    }
}