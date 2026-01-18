<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \App\Models\User::updateOrCreate(
            ['email' => 'osama.asmi04@gmail.com'],
            [
                'name' => 'Osama',
                'lastname' => 'asmi',
                'password' => Hash::make('12345678')
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'Tmmpsacc@outlook.com'],
            [
                'name' => 'Tamz',
                'lastname' => 'id',
                'password' => Hash::make('1234567890')
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'ledegreef07@gmail.com'],
            [
                'name' => 'Lukas',
                'lastname' => 'id',
                'password' => Hash::make('12345678')
            ]
        );
    }
}

