<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use Illuminate\Database\Seeder;

class LeaveRequestSeeder extends Seeder
{
    public function run()
    {
        LeaveRequest::factory()->count(20)->create();
    }
}
