<?php

namespace Tests\Unit;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveServiceTest extends TestCase
{
    use RefreshDatabase;

    private LeaveService $leaveService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->leaveService = new LeaveService();
    }

    public function test_calculate_working_hours_between_excludes_weekends()
    {
        $start = '2023-01-01'; // Sunday
        $end = '2023-01-07'; // Saturday
        $hours = $this->leaveService->calculateWorkingHoursBetween($start, $end);
        // 5 weekdays: Mon-Fri
        $this->assertEquals(40.0, $hours);
    }

    public function test_calculate_working_hours_between_excludes_holidays()
    {
        $start = '2023-01-01'; // Sunday
        $end = '2023-01-07'; // Saturday
        $holidays = ['2023-01-02']; // Monday holiday
        $hours = $this->leaveService->calculateWorkingHoursBetween($start, $end, $holidays);
        // 4 weekdays: Tue-Fri
        $this->assertEquals(32.0, $hours);
    }

    public function test_get_used_hours_sums_duration_hours_for_approved_requests()
    {
        $user = User::factory()->create();
        $leaveType = LeaveType::factory()->create(['deducts_from_balance' => true]);

        LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'status' => LeaveRequest::STATUS_APPROVED,
            'duration_hours' => 16.0,
            'approved_at' => now(),
        ]);

        LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'status' => LeaveRequest::STATUS_APPROVED,
            'duration_hours' => 24.0,
            'approved_at' => now(),
        ]);

        $used = $this->leaveService->getUsedHours($user->id);
        $this->assertEquals(40.0, $used);
    }

    public function test_get_used_hours_ignores_non_deducting_leave_types()
    {
        $user = User::factory()->create();
        $deductingType = LeaveType::factory()->create(['deducts_from_balance' => true]);
        $nonDeductingType = LeaveType::factory()->create(['deducts_from_balance' => false]);

        LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'leave_type_id' => $deductingType->id,
            'status' => LeaveRequest::STATUS_APPROVED,
            'duration_hours' => 16.0,
            'approved_at' => now(),
        ]);

        LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'leave_type_id' => $nonDeductingType->id,
            'status' => LeaveRequest::STATUS_APPROVED,
            'duration_hours' => 24.0,
            'approved_at' => now(),
        ]);

        $used = $this->leaveService->getUsedHours($user->id);
        $this->assertEquals(16.0, $used);
    }

    public function test_get_remaining_hours_calculates_correctly()
    {
        $user = User::factory()->create();
        $leaveType = LeaveType::factory()->create(['deducts_from_balance' => true]);

        LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'status' => LeaveRequest::STATUS_APPROVED,
            'duration_hours' => 40.0,
            'approved_at' => now(),
        ]);

        $balance = $this->leaveService->getRemainingHours($user->id);

        $this->assertEquals(160.0, $balance['remaining_hours']); // 200 - 40
        $this->assertEquals(20.0, $balance['remaining_days']); // 160 / 8
        $this->assertEquals(40.0, $balance['used_hours']);
        $this->assertEquals(200.0, $balance['start_hours']);
    }

    public function test_get_remaining_hours_never_negative()
    {
        $user = User::factory()->create();
        $leaveType = LeaveType::factory()->create(['deducts_from_balance' => true]);

        LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'status' => LeaveRequest::STATUS_APPROVED,
            'duration_hours' => 250.0, // More than 200
            'approved_at' => now(),
        ]);

        $balance = $this->leaveService->getRemainingHours($user->id);

        $this->assertEquals(0.0, $balance['remaining_hours']);
        $this->assertEquals(0.0, $balance['remaining_days']);
    }
}
