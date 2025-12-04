<?php

namespace Database\Seeders;

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
    }
}
