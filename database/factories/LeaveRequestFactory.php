<?php

namespace Database\Factories;

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
        // Maakt een startdatum tussen vandaag en één maand vanaf nu
        $startDate = $this->faker->dateTimeBetween('+0 days', '+1 month');
        // Maakt een einddatum die 1 tot 29 dagen na de startdatum ligt
        $endDate = (clone $startDate)->modify('+' . rand(1, 29) . 'days');

        return [
            'employee_id' => 8,
            'manager_id' => 7,
            'type' => $this->faker->randomElement(['ziek', 'vakantie', 'ander']),
            'reason' => $this->faker->sentence(10),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'ingediend',
            'notification_sent' => false,
        ];
    }
}
