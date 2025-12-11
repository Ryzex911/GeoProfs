<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveRequestsSeeder extends Seeder
{
    public function run()
    {
        DB::table('leave_requests')->insert([
            [
                'employee_id' => 1,
                'leave_type_id' => 3,
                'reason' => 'Summer holiday',
                'start_date' => '2025-07-12',
                'end_date' => '2025-07-22',
                'status' => 'goedgekeurd',
                'submitted_at' => Carbon::now()->subDays(10),
                'approved_at' => Carbon::now()->subDays(8),
                'approved_by' => 3,
                'canceled_at' => null,
                'notification_sent' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'employee_id' => 2,
                'leave_type_id' => 3,
                'reason' => 'Flu symptoms',
                'start_date' => '2025-03-01',
                'end_date' => '2025-03-04',
                'status' => 'afgewezen',
                'submitted_at' => Carbon::now()->subDays(3),
                'approved_at' => null,
                'approved_by' => null,
                'canceled_at' => null,
                'notification_sent' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'employee_id' => 4,
                'leave_type_id' => 2,
                'reason' => 'Family commitment',
                'start_date' => '2025-05-10',
                'end_date' => '2025-05-11',
                'status' => 'geannuleerd',
                'submitted_at' => Carbon::now()->subDays(20),
                'approved_at' => null,
                'approved_by' => null,
                'canceled_at' => Carbon::now()->subDays(18),
                'notification_sent' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
