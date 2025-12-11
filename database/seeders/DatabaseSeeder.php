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
            UserSeeder::class,
        ]);
        LeaveRequest::factory(3)->create();

    }


}
