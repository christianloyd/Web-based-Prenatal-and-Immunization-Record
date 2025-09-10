<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\PrenatalRecord;
use App\Models\PrenatalVisit;
use App\Models\PrenatalAppointment;
use Carbon\Carbon;

class PrenatalRecordSeeder extends Seeder
{
    public function run()
    {
        $patients = [
            ['name' => 'Sarah Williams','age' => 28,'contact' => '+63 912 345 6789','emergency_contact' => '+63 923 111 2222','address'=> '123 Main St, Barangay San Antonio, Davao City','occupation' => 'Teacher'],
            ['name'=> 'Maria Rodriguez','age'=> 32,'contact'=> '+63 915 123 4567','emergency_contact' => '+63 921 444 5555','address'=> '456 Oak Ave, Barangay Poblacion, Davao City','occupation' => 'Nurse'],
            ['name'=> 'Anna Cruz','age'=> 24,'contact'=> '+63 917 555 6677','emergency_contact' => '+63 918 111 3333','address'=> 'Sta. Ana, Davao City','occupation' => 'Cashier'],
            ['name'=> 'Jane Doe','age'=> 29,'contact'=> '+63 916 789 1234','emergency_contact' => '+63 910 222 9999','address'=> 'Toril, Davao City','occupation' => 'Sales Clerk'],
            ['name'=> 'Emily Santos','age'=> 30,'contact'=> '+63 912 333 9876','emergency_contact' => '+63 911 888 7777','address'=> 'Matina, Davao City','occupation' => 'Engineer'],
            ['name'=> 'Rachel Lim','age'=> 27,'contact'=> '+63 913 654 3210','emergency_contact' => '+63 918 765 4321','address'=> 'Buhangin, Davao City','occupation' => 'Secretary'],
            ['name'=> 'Megan Lee','age'=> 33,'contact'=> '+63 917 345 9876','emergency_contact' => '+63 915 876 5432','address'=> 'Lanang, Davao City','occupation' => 'Call Center Agent'],
            ['name'=> 'Patricia Gomez','age'=> 26,'contact'=> '+63 912 123 1111','emergency_contact' => '+63 922 456 7890','address'=> 'Agdao, Davao City','occupation' => 'Vendor'],
            ['name'=> 'Clara Johnson','age'=> 31,'contact'=> '+63 915 555 9999','emergency_contact' => '+63 910 333 4444','address'=> 'Panacan, Davao City','occupation' => 'Housewife'],
            ['name'=> 'Olivia Tan','age'=> 22,'contact'=> '+63 913 888 1111','emergency_contact' => '+63 917 555 1212','address'=> 'Bago Aplaya, Davao City','occupation' => 'Student'],
            ['name'=> 'Sophia Reyes','age'=> 34,'contact'=> '+63 919 222 3333','emergency_contact' => '+63 922 999 8888','address'=> 'Bacaca, Davao City','occupation' => 'Manager'],
            ['name'=> 'Grace Lee','age'=> 25,'contact'=> '+63 910 444 5555','emergency_contact' => '+63 916 111 2222','address'=> 'Sasa, Davao City','occupation' => 'Clerk'],
            ['name'=> 'Hannah Kim','age'=> 27,'contact'=> '+63 917 444 6666','emergency_contact' => '+63 918 888 4444','address'=> 'Mintal, Davao City','occupation' => 'Teacher'],
            ['name'=> 'Isabella Cruz','age'=> 28,'contact'=> '+63 916 333 5555','emergency_contact' => '+63 919 222 7777','address'=> 'Calinan, Davao City','occupation' => 'Cashier'],
            ['name'=> 'Victoria Smith','age'=> 35,'contact'=> '+63 918 777 8888','emergency_contact' => '+63 910 444 6666','address'=> 'Buhangin, Davao City','occupation' => 'Businesswoman'],
            ['name'=> 'Ashley Mendoza','age'=> 21,'contact'=> '+63 919 555 4444','emergency_contact' => '+63 911 777 5555','address'=> 'Catalunan Grande, Davao City','occupation' => 'Student'],
            ['name'=> 'Ella Davis','age'=> 29,'contact'=> '+63 917 111 4444','emergency_contact' => '+63 915 222 8888','address'=> 'Toril, Davao City','occupation' => 'Call Center Agent'],
            ['name'=> 'Mia Torres','age'=> 23,'contact'=> '+63 910 333 1111','emergency_contact' => '+63 918 555 4444','address'=> 'Bunawan, Davao City','occupation' => 'Clerk'],
            ['name'=> 'Camille Brown','age'=> 30,'contact'=> '+63 912 888 2222','emergency_contact' => '+63 917 666 5555','address'=> 'Bangkal, Davao City','occupation' => 'Nurse'],
            ['name'=> 'Nicole White','age'=> 26,'contact'=> '+63 915 222 4444','emergency_contact' => '+63 910 333 5555','address'=> 'Ulas, Davao City','occupation' => 'Cashier'],
        ];

        foreach ($patients as $index => $data) {
            $lastPatient = Patient::orderBy('id', 'desc')->first();
            $nextNumber = $lastPatient ? $lastPatient->id + 1 : 1;

            Patient::create([
                'formatted_patient_id' => 'PT-' . str_pad($nextNumber + $index, 4, '0', STR_PAD_LEFT),
                'name' => $data['name'],
                'age' => $data['age'],
                'contact' => $data['contact'],
                'emergency_contact' => $data['emergency_contact'],
                'address' => $data['address'],
                'occupation' => $data['occupation']
            ]);
        }
    }
}
