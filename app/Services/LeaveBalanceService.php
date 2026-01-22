<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;

class LeaveBalanceService
{
    public function getBalanceDays(int $userId): int
    {
        $balance = LeaveBalance::where('user_id', $userId)->first();
        return $balance ? $balance->balance_days : 25; // default 25 days
    }

    public function updateBalanceDays(int $userId, int $balanceDays): void
    {
        LeaveBalance::updateOrCreate(
            ['user_id' => $userId],
            ['balance_days' => $balanceDays]
        );
    }

    public function getRemainingHours(int $userId): array
    {
        $days = $this->getBalanceDays($userId);
        $hours = $days * 8;
        return [
            'remaining_days' => $days,
            'remaining_hours' => $hours,
        ];
    }
}
