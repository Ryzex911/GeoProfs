<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::create([
            'name' => 'Ryzex',
            'lastname' => 'Alabed',
            'email' => 'ryzexgamer1@gmail.com',
            'password' => 'Ryzex2028@@@@'
        ]);

        $users = User::factory()->count(10)->create();
        foreach ($users as $user) {
            $user->roles()->attach(rand(1, 5));
        }
    }
}
