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
        return $user->hasRole(['admin', 'manager', 'projectleider']);
    }

    public function approve(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'projectleider']);
    }

    public function reject(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'projectleider']);
    }


    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
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
