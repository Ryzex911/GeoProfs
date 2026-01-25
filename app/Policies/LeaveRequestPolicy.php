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
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LeaveRequest $leaveRequest): bool
    {
        return false;
    }
}
