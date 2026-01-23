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
     * Haal gebruikte dagen op voor een gebruiker in een jaar.
     */
    public function getUsedDays(int $userId, int $year = null): float
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

        // Prefer: sum duration_hours if stored, convert to days
        if (\Schema::hasColumn('leave_requests', 'duration_hours')) {
            $totalHours = (float) $query->sum('duration_hours');
            return round($totalHours / 8.0, 2); // Convert hours to days
        }

        // Fallback: compute from dates (slower), convert to days
        $total = 0.0;
        $holidays = config('company.holidays', []);
        foreach ($query->get() as $lr) {
            $total += $this->calculateWorkingHoursBetween($lr->start_date, $lr->end_date, $holidays);
        }
        return round($total / 8.0, 2); // Convert hours to days
    }

    /**
     * Haal startsaldo dagen op voor een gebruiker in een jaar.
     */
    public function getStartSaldoDays(int $userId, int $year = null): float
    {
        // Iedereen heeft 25 dagen vrij per jaar
        return 25.0;
    }

    /**
     * Haal resterend saldo op voor een gebruiker in een jaar.
     * Berekening: remaining_days = start_days - used_days
     * (Geen carry-over meer)
     */
    public function getRemainingDays(int $userId, int $year = null): array
    {
        $year = $year ?? date('Y');
        $startSaldoDays = $this->getStartSaldoDays($userId, $year);
        $used = $this->getUsedDays($userId, $year);

        $remaining = round($startSaldoDays - $used, 2);
        return [
            'remaining_days' => max(0, $remaining),
            'used_days' => $used,
            'start_days' => $startSaldoDays,
        ];
    }

    /**
     * Alias voor backward compatibility: getRemainingHours
     * (Geeft DAGEN terug, niet uren)
     */
    public function getRemainingHours(int $userId, int $year = null): array
    {
        return $this->getRemainingDays($userId, $year);
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
