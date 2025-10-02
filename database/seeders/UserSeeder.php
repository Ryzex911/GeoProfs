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
            'name' => 'Lukas',
            'lastname' => 'de Greef',
            'email' => '1@1.com',
            'password' => '12345678',
        ]);
    }
}
