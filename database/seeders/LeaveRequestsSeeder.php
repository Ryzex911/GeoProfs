<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveRequestSeeder extends Seeder
{
    public function run()
    {
        // Haal wat users en leave_types op (zorg dat UserSeeder & LeaveTypeSeeder al gedraaid zijn)
        $user = DB::table('users')->first(); // employee
        $leaveType = DB::table('leave_types')->where('name', 'Vakantie')->first();

        if (!$user || !$leaveType) {
            // veilig: als er geen data is, stoppen
            return;
        }

        DB::table('leave_requests')->insert([
            'employee_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'reason' => 'Seediing voorbeeld',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 'ingediend',
            'proof' => null,
            'submitted_at' => now(),
            'notification_sent' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
