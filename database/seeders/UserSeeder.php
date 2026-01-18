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
    User::updateOrCreate(
        ['email' => 'osama.asmi04@gmail.com'],
        [
            'name' => 'Osama',
            'lastname' => 'asmi',
            'password' => Hash::make('12345678')
        ]
    );

    User::updateOrCreate(
        ['email' => 'Tmmpsacc@outlook.com'],
        [
            'name' => 'Tamz',
            'lastname' => 'id',
            'password' => Hash::make('1234567890')
        ]
    );

    User::updateOrCreate(
        ['email' => 'ledegreef07@gmail.com'],
        [
            'name' => 'Lukas',
            'lastname' => 'id',
            'password' => Hash::make('12345678')
        ]
    );

    $users = User::factory()->count(10)->create();
    foreach ($users as $user) {
        $user->roles()->attach(rand(1, 5));
    }
}
}
