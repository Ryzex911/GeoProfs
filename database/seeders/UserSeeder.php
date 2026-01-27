<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
{
    User::create([
        'name' => 'Osama',
        'lastname' => 'asmi',
        'email' => 'osama.asmi04@gmail.com',
        'password' => Hash::make('12345678')
    ]);

    User::create([
        'name' => 'Tamz',
        'lastname' => 'id',
        'email' => 'Tmmpsacc@outlook.com',
        'password' => Hash::make('1234567890')
    ]);
    User::create([
        'name' => 'Lukas',
        'lastname' => 'id',
        'email' => 'ledegreef07@gmail.com',
        'password' => Hash::make('12345678')
    ]);

    $users = User::factory()->count(10)->create();
    foreach ($users as $user) {
        $user->roles()->attach(rand(1, 5));
    }
}
}
