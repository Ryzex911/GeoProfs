<?php

namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal LeaveBalanceService for US007 (days-only, no carry-over)
 */
class LeaveBalanceService
{
    /**
     * Startsaldo in dagen voor een gebruiker in een jaar.
     * Fulltime = 25 dagen. Pro-rata op basis van contract_fte and start_date.
     */
    public function getStartSaldoDays(User $user, int $year): float
    {
        $contractFte = $user->contract_fte ?? 1.0;

        // Determine the start date to use for pro-rata
        $userStart = null;
        if (!empty($user->start_date)) {
            $userStart = Carbon::parse($user->start_date)->startOfDay();
        }

        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();

        // If user start is within the year, pro-rata from their start date, otherwise full year
        $startForCalc = $userStart && $userStart->between($yearStart, $yearEnd) ? $userStart : $yearStart;

        $yearDays = $yearStart->isLeapYear() ? 366 : 365;

        $remainingDays = $startForCalc->diffInDays($yearEnd) + 1; // inclusive

        $startsaldodays = 25.0 * $contractFte * ($remainingDays / $yearDays);

        return round($startsaldodays, 4);
    }

    /**
     * Gebruikte dagen in een jaar. Sums duration_days if present, else converts duration_hours/8.
     */
    public function getUsedDays(User $user, int $year): float
    {
        $query = LeaveRequest::query()
            ->where('employee_id', $user->id)
            ->where('status', LeaveRequest::STATUS_APPROVED)
            ->whereHas('leaveType', function ($q) {
                $q->where('deducts_from_balance', true);
            })
            ->whereYear('approved_at', $year);

        // Prefer duration_days column
        if (Schema::hasColumn('leave_requests', 'duration_days')) {
            return (float) $query->sum('duration_days');
        }

        // Fallback: use duration_hours if present and convert to days
        if (Schema::hasColumn('leave_requests', 'duration_hours')) {
            $hours = (float) $query->sum('duration_hours');
            return round($hours / 8.0, 4);
        }

        // Last fallback: compute from dates using existing LeaveService helper
        $leaveService = app(LeaveService::class);
        $holidays = config('company.holidays', []);
        $totalHours = 0.0;
        foreach ($query->get() as $lr) {
            $totalHours += $leaveService->calculateWorkingHoursBetween($lr->start_date, $lr->end_date, $holidays);
        }
        return round($totalHours / 8.0, 4);
    }

    /**
     * Resterend saldo in dagen voor een gebruiker in een jaar.
     */
    public function getRemainingDays(User $user, int $year): float
    {
        $start = $this->getStartSaldoDays($user, $year);
        $used = $this->getUsedDays($user, $year);

        return round(max(0, $start - $used), 4);
    }
}
