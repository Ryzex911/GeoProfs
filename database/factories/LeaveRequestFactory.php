<?php
use App\Models\User;
use App\Models\LeaveType;

$factory->define(App\Models\LeaveRequest::class, function (Faker\Generator $faker) {
    $user = User::inRandomOrder()->first();
    $type = LeaveType::inRandomOrder()->first();

    return [
        'employee_id' => $user->id,
        'leave_type_id' => $type->id,
        'reason' => $faker->sentence(),
        'start_date' => now()->addDays(rand(1,30))->toDateString(),
        'end_date' => now()->addDays(rand(31,40))->toDateString(),
        'status' => 'ingediend',
        'submitted_at' => now(),
        'notification_sent' => false,
    ];
});
