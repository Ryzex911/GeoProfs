<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \App\Models\User::create([
            'name' => 'Tawfik',
            'lastname' => 'Alabed',
            'email' => 'tawfikalabed2021@gmail.com',
            'password' => '12345678',
        ]);
    }
}
