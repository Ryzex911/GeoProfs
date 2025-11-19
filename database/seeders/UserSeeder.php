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
        $users = User::factory()->count(10)->create();
        foreach ($users as $user) {
            $user->roles()->attach(rand(1, 5));
        }
    }
}
