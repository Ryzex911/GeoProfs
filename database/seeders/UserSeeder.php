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
            'email' => 'test@example.com',
            'password' => bcrypt('wachtwoord123'),
        ]);
    }
}
