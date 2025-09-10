<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = [
            // Pregnant patients at different stages
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'date_of_birth' => Carbon::parse('1995-03-15'),
                'address' => 'Barangay San Miguel, Quezon City',
                'contact_number' => '09171234567',
                'emergency_contact' => '09187654321',
                'emergency_contact_name' => 'Juan Santos',
                'emergency_contact_relationship' => 'Husband',
                'medical_history' => 'Previous normal delivery, no complications',
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Cruz',
                'date_of_birth' => Carbon::parse('1992-07-20'),
                'address' => 'Barangay Bagong Pag-asa, Manila',
                'contact_number' => '09281234567',
                'emergency_contact' => '09287654321',
                'emergency_contact_name' => 'Pedro Cruz',
                'emergency_contact_relationship' => 'Husband',
                'medical_history' => 'First pregnancy, healthy',
            ],
            [
                'first_name' => 'Rosa',
                'last_name' => 'Garcia',
                'date_of_birth' => Carbon::parse('1990-11-08'),
                'address' => 'Barangay Maligaya, Pasig City',
                'contact_number' => '09391234567',
                'emergency_contact' => '09397654321',
                'emergency_contact_name' => 'Roberto Garcia',
                'emergency_contact_relationship' => 'Husband',
                'medical_history' => 'History of hypertension, monitored',
            ],
            [
                'first_name' => 'Luz',
                'last_name' => 'Reyes',
                'date_of_birth' => Carbon::parse('1988-05-12'),
                'address' => 'Barangay San Antonio, Makati City',
                'contact_number' => '09401234567',
                'emergency_contact' => '09407654321',
                'emergency_contact_name' => 'Carlos Reyes',
                'emergency_contact_relationship' => 'Husband',
                'medical_history' => 'Second pregnancy, previous C-section',
            ],
            [
                'first_name' => 'Elena',
                'last_name' => 'Torres',
                'date_of_birth' => Carbon::parse('1993-09-25'),
                'address' => 'Barangay Santolan, Marikina City',
                'contact_number' => '09511234567',
                'emergency_contact' => '09517654321',
                'emergency_contact_name' => 'Miguel Torres',
                'emergency_contact_relationship' => 'Husband',
                'medical_history' => 'Third pregnancy, two previous normal deliveries',
            ],
            // Patients with completed pregnancies (now with children)
            [
                'first_name' => 'Carmen',
                'last_name' => 'Mendoza',
                'date_of_birth' => Carbon::parse('1991-01-18'),
                'address' => 'Barangay Libis, Quezon City',
                'contact_number' => '09621234567',
                'emergency_contact' => '09627654321',
                'emergency_contact_name' => 'Luis Mendoza',
                'emergency_contact_relationship' => 'Husband',
                'medical_history' => 'Recently delivered healthy baby, no complications',
            ],
            [
                'first_name' => 'Isabel',
                'last_name' => 'Villanueva',
                'date_of_birth' => Carbon::parse('1994-12-03'),
                'address' => 'Barangay Kamuning, Quezon City',
                'contact_number' => '09731234567',
                'emergency_contact' => '09737654321',
                'emergency_contact_name' => 'Antonio Villanueva',
                'emergency_contact_relationship' => 'Husband',
                'medical_history' => 'Delivered 6 months ago, breastfeeding well',
            ],
            [
                'first_name' => 'Gloria',
                'last_name' => 'Ramos',
                'date_of_birth' => Carbon::parse('1989-08-14'),
                'address' => 'Barangay Teachers Village, Quezon City',
                'contact_number' => '09841234567',
                'emergency_contact' => '09847654321',
                'emergency_contact_name' => 'Francisco Ramos',
                'emergency_contact_relationship' => 'Husband',
                'medical_history' => 'Delivered twins 1 year ago, both healthy',
            ],
            // Regular patients for general healthcare
            [
                'first_name' => 'Esperanza',
                'last_name' => 'Flores',
                'date_of_birth' => Carbon::parse('1987-04-22'),
                'address' => 'Barangay Ugong Norte, Quezon City',
                'contact_number' => '09951234567',
                'emergency_contact' => '09957654321',
                'emergency_contact_name' => 'Jose Flores',
                'emergency_contact_relationship' => 'Husband',
                'medical_history' => 'Planning for pregnancy, pre-conception counseling',
            ],
            [
                'first_name' => 'Tessie',
                'last_name' => 'Aquino',
                'date_of_birth' => Carbon::parse('1985-10-30'),
                'address' => 'Barangay Holy Spirit, Quezon City',
                'contact_number' => '09061234567',
                'emergency_contact' => '09067654321',
                'emergency_contact_name' => 'Benjamin Aquino',
                'emergency_contact_relationship' => 'Husband',
                'medical_history' => 'Family planning consultation, healthy',
            ],
        ];

        foreach ($patients as $patientData) {
            Patient::create($patientData);
        }
    }
}