<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Schema;

class LeaveService
{
    /**
     * Bereken werkuren tussen twee datums (exclusief weekends en feestdagen).
     */
    public function calculateWorkingHoursBetween(string $startDate, string $endDate, array $holidays = [], float $hoursPerDay = 8.0): float
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();
        if ($end->lessThan($start)) return 0.0;

        $period = CarbonPeriod::create($start, $end);
        $days = 0;
        foreach ($period as $date) {
            $iso = $date->toDateString();
            if (in_array($iso, $holidays)) continue;
            if ($date->isWeekend()) continue;
            $days++;
        }
        return round($days * $hoursPerDay, 2);
    }

    /**
     * Haal gebruikte uren op voor een gebruiker in een jaar.
     */
    public function getUsedHours(int $userId, int $year = null): float
    {
        $query = LeaveRequest::query()
            ->where('employee_id', $userId)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->whereHas('leaveType', function($q) {
                $q->where('deducts_from_balance', true);
            });

        if ($year) {
            $query->whereYear('approved_at', $year);
        }

        // Prefer: sum duration_hours if stored
        if (\Schema::hasColumn('leave_requests', 'duration_hours')) {
            return (float) $query->sum('duration_hours');
        }

        // Fallback: compute from dates (slower)
        $total = 0.0;
        $holidays = config('company.holidays', []);
        foreach ($query->get() as $lr) {
            $total += $this->calculateWorkingHoursBetween($lr->start_date, $lr->end_date, $holidays);
        }
        return round($total, 2);
    }

    /**
     * Haal startsaldo uren op voor een gebruiker in een jaar.
     */
    public function getStartSaldoHours(int $userId, int $year = null): float
    {
        // Voor nu: standaard 25 dagen = 200 uur
        // TODO: pro-rata op basis van contract, startdatum gebruiker
        $startsaldodays = 25.0;
        $hoursPerDay = 8.0;
        return $startsaldodays * $hoursPerDay;
    }

    /**
     * Haal carry-over uren op voor een gebruiker in een jaar.
     */
    public function getCarryOverHours(int $userId, int $year = null): float
    {
        // Voor nu: 0, later implementeren
        // Max 5 dagen = 40 uur
        return 0.0;
    }

    /**
     * Haal resterend saldo op voor een gebruiker in een jaar.
     */
    public function getRemainingHours(int $userId, int $year = null): array
    {
        $year = $year ?? date('Y');
        $hoursPerDay = 8.0;
        $startSaldoHours = $this->getStartSaldoHours($userId, $year);
        $carryOver = $this->getCarryOverHours($userId, $year);
        $used = $this->getUsedHours($userId, $year);

        $remaining = round($startSaldoHours + $carryOver - $used, 2);
        return [
            'remaining_hours' => max(0, $remaining),
            'remaining_days' => round(max(0, $remaining) / $hoursPerDay, 2),
            'used_hours' => $used,
            'start_hours' => $startSaldoHours,
            'carryover_hours' => $carryOver,
        ];
    }

    /**
     * Bereken en sla duration_hours op bij goedkeuring.
     */
    public function calculateAndSaveDurationHours(LeaveRequest $leaveRequest): void
    {
        $holidays = config('company.holidays', []);
        $duration = $this->calculateWorkingHoursBetween($leaveRequest->start_date, $leaveRequest->end_date, $holidays);
        $leaveRequest->duration_hours = $duration;
        $leaveRequest->save();
    }
}
