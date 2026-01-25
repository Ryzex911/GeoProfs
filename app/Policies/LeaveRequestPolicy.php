<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    /**
     * Manager board openen
     */
    public function leaveApprovePage(User $user): bool
    {
        // ✅ Niet afhankelijk van active_role_id, maar echte rollen
        return $user->hasRole(['admin', 'manager', 'projectleider']);
    }

    /**
     * Approve (manager/projectleider/admin)
     */
    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasRole(['admin', 'manager', 'projectleider']);
    }

    /**
     * Reject (manager/projectleider/admin)
     */
    public function reject(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->hasRole(['admin', 'manager', 'projectleider']);
    }
}
