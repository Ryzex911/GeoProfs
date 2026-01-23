<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLeaveBalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_correct_json()
    {
        $year = 2024;
        $user = User::factory()->create(['contract_fte' => 1.0, 'start_date' => '2024-01-01']);
        $type = LeaveType::factory()->create(['deducts_from_balance' => true]);

        LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'leave_type_id' => $type->id,
            'status' => LeaveRequest::STATUS_APPROVED,
            'duration_hours' => 16.0,
            'approved_at' => now()->year($year),
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/leave-balance?year=' . $year)
            ->assertStatus(200)
            ->assertJsonStructure(['year','startsaldo_days','used_days','remaining_days'])
            ->assertJsonFragment(['year' => $year]);
    }
}
