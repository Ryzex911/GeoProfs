<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
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
        'password' => Hash::make('12345678')
    ]);

    \App\Models\User::create([
        'name' => 'Tamz',
        'lastname' => 'id',
        'email' => 'Tmmpsacc@outlook.com',
        'password' => Hash::make('1234567890')
    ]);
}

}
