<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveRequestSeeder extends Seeder
{
    public function run()
    {
        DB::table('leave_requests')->insert([
            // TC-MA01: Pending request from Piet (approval test)
            [
                'employee_id' => 2,
                'leave_type_id' => 1,
                'reason' => 'Vakantie',
                'start_date' => Carbon::now()->addDays(10),
                'end_date' => Carbon::now()->addDays(17),
                'status' => 'pending',
                'submitted_at' => Carbon::now()->subDays(2),
                'approved_at' => null,
                'approved_by' => null,
                'canceled_at' => null,
                'notification_sent' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // TC-MA02: Pending request from Sara (rejection test)
            [
                'employee_id' => 3,
                'leave_type_id' => 2,
                'reason' => 'Ziektedag',
                'start_date' => Carbon::now()->addDays(3),
                'end_date' => Carbon::now()->addDays(4),
                'status' => 'pending',
                'submitted_at' => Carbon::now()->subDays(1),
                'approved_at' => null,
                'approved_by' => null,
                'canceled_at' => null,
                'notification_sent' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // TC-MA03: Approved request from Piet (filter test)
            [
                'employee_id' => 2,
                'leave_type_id' => 1,
                'reason' => 'Vakantie',
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->subDays(25),
                'status' => 'approved',
                'submitted_at' => Carbon::now()->subDays(35),
                'approved_at' => Carbon::now()->subDays(32),
                'approved_by' => 1,
                'canceled_at' => null,
                'notification_sent' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // TC-MA03: Rejected request from Sara (filter test)
            [
                'employee_id' => 3,
                'leave_type_id' => 3,
                'reason' => 'Persoonlijke reden',
                'start_date' => Carbon::now()->subDays(20),
                'end_date' => Carbon::now()->subDays(18),
                'status' => 'rejected',
                'submitted_at' => Carbon::now()->subDays(25),
                'approved_at' => null,
                'approved_by' => null,
                'canceled_at' => null,
                'notification_sent' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
