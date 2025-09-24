<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Midwife Users
            [
                'name' => 'Maria Elena Rodriguez',
                'gender' => 'female',
                'age' => 32,
                'username' => 'midwife1',
                'contact_number' => '09171234567',
                'address' => 'Brgy. Mecolong Health Center, Dumalinao, Zamboanga del Sur',
                'role' => 'midwife',
                'is_active' => true,
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Carmen Santos Reyes',
                'gender' => 'female',
                'age' => 28,
                'username' => 'midwife2',
                'contact_number' => '09281234567',
                'address' => 'Brgy. Mecolong Health Center, Dumalinao, Zamboanga del Sur',
                'role' => 'midwife',
                'is_active' => true,
                'password' => Hash::make('password123'),
            ],

            // BHW (Barangay Health Worker) Users
            [
                'name' => 'Rosa Gutierrez Cruz',
                'gender' => 'female',
                'age' => 45,
                'username' => 'bhw1',
                'contact_number' => '09391234567',
                'address' => 'Purok 1, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
                'role' => 'bhw',
                'is_active' => true,
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Ana Lucia Torres',
                'gender' => 'female',
                'age' => 38,
                'username' => 'bhw2',
                'contact_number' => '09451234567',
                'address' => 'Purok 2, Brgy. Mecolong, Dumalinao, Zamboanga del Sur',
                'role' => 'bhw',
                'is_active' => true,
                'password' => Hash::make('password123'),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}