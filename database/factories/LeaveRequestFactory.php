<?php

namespace Database\Factories;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $startDate = $this->faker->dateTimeBetween('+0 days', '+1 month');
        $endDate = (clone $startDate)->modify('+' . rand(1, 29) . 'days');

        $user = User::inRandomOrder()->first();
        $type = LeaveType::inRandomOrder()->first();

        return [
            'employee_id' => $user->id,
            'leave_type_id' => $type->id,
            'reason' => $this->faker->sentence(10),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'proof' => null,
            'status' => 'ingediend',
            'submitted_at' => now(),
            'approved_at' => null,
            'approved_by' => null,
            'canceled_at' => null,
            'notification_sent' => false,
        ];
    }
}
