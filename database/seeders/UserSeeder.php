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
            'name' => 'Osama',
            'lastname' => 'asmi',
            'email' => 'osama.asmi04@gmail.com',
            'password' => '12345678'
        ]);
    }
}
