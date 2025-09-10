<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChildRecord;
use App\Models\Patient;

class ChildRecordSeeder extends Seeder
{
    public function run(): void
    {
        // Make sure these mothers exist in patients table
        $maria = Patient::firstOrCreate(['name' => 'Maria Dela Cruz'], [
            'formatted_patient_id' => 'PT-MAR0001',
            'age' => 30,
        ]);

        $luz = Patient::firstOrCreate(['name' => 'Luz Santos'], [
            'formatted_patient_id' => 'PT-LUZ0002',
            'age' => 28,
        ]);

        ChildRecord::create([
            'child_name'   => 'Juan Dela Cruz',
            'gender'       => 'Male',
            'birth_height' => 50.5,
            'birth_weight' => 3.2,
            'birthdate'    => '2020-05-15',
            'birthplace'   => 'Manila',
            'address'      => '123 Sampaguita St, Quezon City',
            'mother_id'    => $maria->id,
            'father_name'  => 'Jose Dela Cruz',
            'phone_number' => '09171234567',
        ]);

        ChildRecord::create([
            'child_name'   => 'Ana Santos',
            'gender'       => 'Female',
            'birth_height' => 48.0,
            'birth_weight' => 2.9,
            'birthdate'    => '2021-11-03',
            'birthplace'   => 'Cebu City',
            'address'      => '456 Mabini St, Cebu City',
            'mother_id'    => $luz->id,
            'father_name'  => 'Pedro Santos',
            'phone_number' => '09981234567',
        ]);
    }
}
