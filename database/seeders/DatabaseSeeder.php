<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roep de UserSeeder aan (maakt o.a. Tawfik aan)
        $this->call([
            RoleSeeder::class,
        ]);

        $this->call([
            UserSeeder::class,
        ]);
        $this->call([
            LeaveTypeSeeder::class,
        ]);

        // Gebruik E2eSeeder voor E2E tests, anders normale seeders
        if (env('E2E_TESTING', false)) {
            $this->call([
                E2eSeeder::class,
            ]);
        } else {
            LeaveRequest::factory(3)->create();

            $this->call([
                LeaveTypeSeeder::class,
            ]);

            $this->call([
                LeaveRequestSeeder::class,
            ]);
        }
    }


}
