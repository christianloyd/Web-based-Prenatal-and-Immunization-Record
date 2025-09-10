<?php
// database/seeders/MidwifeSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MidwifeSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'full_name' => 'Midwife User',
            'gender'    => 'female',
            'age'       => 35,
            'username'  => 'midwife',
            'role'      => 'midwife',
            'password'  => Hash::make('midwife123'),
        ]);

        User::create([
            'full_name' => 'Barangay Health Worker User',
            'gender'    => 'female',
            'age'       => 28,
            'username'  => 'bhworker',
            'role'      => 'bhw',
            'password'  => Hash::make('bhw123'),
        ]);
    }
}