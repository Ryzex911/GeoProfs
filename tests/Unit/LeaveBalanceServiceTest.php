<?php

namespace Tests\Unit;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveBalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private LeaveBalanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LeaveBalanceService();
    }

    public function test_fulltime_gets_25_days()
    {
        $user = User::factory()->create(['contract_fte' => 1.0, 'start_date' => '2024-01-01']);

        $start = $this->service->getStartSaldoDays($user, 2024);
        $this->assertEqualsWithDelta(25.0, (float)$start, 0.1);
    }

    public function test_parttime_pro_rata()
    {
        $user = User::factory()->create(['contract_fte' => 0.5, 'start_date' => '2024-01-01']);

        $start = $this->service->getStartSaldoDays($user, 2024);
        $this->assertEqualsWithDelta(12.5, (float)$start, 0.1);
    }

    public function test_start_mid_year_pro_rata()
    {
        $year = 2024;
        $user = User::factory()->create(['contract_fte' => 1.0, 'start_date' => '2024-07-01']);

        $start = $this->service->getStartSaldoDays($user, $year);

        $yearStart = Carbon::create($year,1,1);
        $yearEnd = Carbon::create($year,12,31);
        $remainingDays = Carbon::parse('2024-07-01')->diffInDays($yearEnd) + 1;
        $yearDays = $yearStart->isLeapYear() ? 366 : 365;
        $expected = 25.0 * 1.0 * ($remainingDays / $yearDays);

        $this->assertEqualsWithDelta($expected, $start, 0.1);
    }

    public function test_used_days_and_remaining()
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

        LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'leave_type_id' => $type->id,
            'status' => LeaveRequest::STATUS_APPROVED,
            'duration_hours' => 24.0,
            'approved_at' => now()->year($year),
        ]);

        $used = $this->service->getUsedDays($user, $year);
        $this->assertEquals(5.0, $used);

        $remaining = $this->service->getRemainingDays($user, $year);
        $this->assertEqualsWithDelta(20.0, (float)$remaining, 0.1);
    }

    public function test_only_approved_and_deducting_count()
    {
        $year = 2024;
        $user = User::factory()->create(['contract_fte' => 1.0, 'start_date' => '2024-01-01']);
        $deduct = LeaveType::factory()->create(['deducts_from_balance' => true]);
        $nodeduct = LeaveType::factory()->create(['deducts_from_balance' => false]);

        // approved & deducting -> counts
        LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'leave_type_id' => $deduct->id,
            'status' => LeaveRequest::STATUS_APPROVED,
            'duration_hours' => 8.0,
            'approved_at' => now()->year($year),
        ]);

        // approved but non-deducting -> ignored
        LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'leave_type_id' => $nodeduct->id,
            'status' => LeaveRequest::STATUS_APPROVED,
            'duration_hours' => 8.0,
            'approved_at' => now()->year($year),
        ]);

        // pending & deducting -> ignored
        LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'leave_type_id' => $deduct->id,
            'status' => LeaveRequest::STATUS_PENDING,
            'duration_hours' => 8.0,
        ]);

        $used = $this->service->getUsedDays($user, $year);
        $this->assertEquals(1.0, $used);
    }
}
