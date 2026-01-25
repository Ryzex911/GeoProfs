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

    /**
     * Test: calculateDurationHours werkt correct voor werkdagen
     */
    public function test_calculate_duration_hours_for_weekdays()
    {
        $user = User::factory()->create();
        // Maandag 1 januari 2024 tot Woensdag 3 januari 2024 = 3 werkdagen = 24 uur
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'start_date' => Carbon::create(2024, 1, 1),
            'end_date' => Carbon::create(2024, 1, 3),
        ]);

        // Act
        $hours = $this->service->calculateDurationHours($leaveRequest);

        // Assert
        $this->assertEquals(24.0, $hours);
    }

    /**
     * Test: calculateDurationHours excludeert weekends
     */
    public function test_calculate_duration_hours_excludes_weekends()
    {
        $user = User::factory()->create();
        // Vrijdag 5 januari tot Maandag 8 januari 2024
        // = Vrijdag + Zaterdag (skip) + Zondag (skip) + Maandag = 2 werkdagen = 16 uur
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'start_date' => Carbon::create(2024, 1, 5),
            'end_date' => Carbon::create(2024, 1, 8),
        ]);

        // Act
        $hours = $this->service->calculateDurationHours($leaveRequest);

        // Assert
        $this->assertEquals(16.0, $hours);
    }

    /**
     * Test: calculateDurationHours = 0 als einde voor begin
     */
    public function test_calculate_duration_hours_returns_zero_if_end_before_start()
    {
        $user = User::factory()->create();
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'start_date' => Carbon::create(2024, 1, 10),
            'end_date' => Carbon::create(2024, 1, 5),  // Voor start_date
        ]);

        // Act
        $hours = $this->service->calculateDurationHours($leaveRequest);

        // Assert
        $this->assertEquals(0.0, $hours);
    }

    /**
     * Test: calculateDurationHours voor enkel weekend = 0
     */
    public function test_calculate_duration_hours_returns_zero_for_weekend_only()
    {
        $user = User::factory()->create();
        // Zaterdag 6 januari tot Zondag 7 januari 2024 = pure weekend
        $leaveRequest = LeaveRequest::factory()->create([
            'employee_id' => $user->id,
            'start_date' => Carbon::create(2024, 1, 6),
            'end_date' => Carbon::create(2024, 1, 7),
        ]);

        // Act
        $hours = $this->service->calculateDurationHours($leaveRequest);

        // Assert
        $this->assertEquals(0.0, $hours);
    }

    /**
     * Test: getRemainingForUser geeft correct array terug
     */
    public function test_get_remaining_for_user_returns_correct_structure()
    {
        $user = User::factory()->create(['contract_fte' => 1.0, 'start_date' => '2024-01-01']);

        // Act
        $balance = $this->service->getRemainingForUser($user, 2024);

        // Assert
        $this->assertArrayHasKey('remaining_days', $balance);
        $this->assertArrayHasKey('remaining_hours', $balance);
        $this->assertArrayHasKey('start_days', $balance);
        $this->assertArrayHasKey('start_hours', $balance);
        $this->assertArrayHasKey('used_days', $balance);
        $this->assertArrayHasKey('used_hours', $balance);
        $this->assertArrayHasKey('year', $balance);

        // Uren moeten ~8× de dagen zijn
        $this->assertEqualsWithDelta(
            $balance['remaining_hours'] / 8.0,
            $balance['remaining_days'],
            0.01
        );
    }
}
