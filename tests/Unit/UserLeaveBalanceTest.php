<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\LeaveService;
use Tests\TestCase;

class UserLeaveBalanceTest extends TestCase
{
    public function test_get_leave_balance_method_exists()
    {
        $user = new User();
        $this->assertTrue(method_exists($user, 'getLeaveBalance'));
    }

    public function test_get_leave_balance_returns_array_structure()
    {
        $user = new User();
        $user->id = 1; // Set ID manually for testing

        // Mock the LeaveService
        $mockService = $this->mock(LeaveService::class);
        $mockService->shouldReceive('getRemainingDays')
            ->once()
            ->with(1)
            ->andReturn([
                'remaining_days' => 20.0,
                'used_days' => 5.0,
                'start_days' => 25.0,
            ]);

        $balance = $user->getLeaveBalance();

        $this->assertIsArray($balance);
        $this->assertArrayHasKey('remaining_days', $balance);
        $this->assertArrayHasKey('used_days', $balance);
        $this->assertArrayHasKey('start_days', $balance);
        $this->assertEquals(20.0, $balance['remaining_days']);
    }

    public function test_get_leave_balance_calls_service_with_correct_user_id()
    {
        $user = new User();
        $user->id = 123;

        // Mock the LeaveService
        $mockService = $this->mock(LeaveService::class);
        $mockService->shouldReceive('getRemainingDays')
            ->once()
            ->with(123)
            ->andReturn([
                'remaining_days' => 20.0,
                'used_days' => 5.0,
                'start_days' => 25.0,
            ]);

        $balance = $user->getLeaveBalance();

        $this->assertEquals(20.0, $balance['remaining_days']);
        $this->assertEquals(5.0, $balance['used_days']);
    }
}
