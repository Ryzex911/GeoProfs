<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function leaveApprovePage(User $user): bool
    {
        return $user->activeRoleIs(['manager', 'projectleider']);
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
