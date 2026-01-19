<?php

namespace App\Observers;

use App\Mail\LeaveRequestStatusChanged;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestSubmitted;
use Illuminate\Support\Facades\Mail;

class LeaveRequestObserver
{
    public function created(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->loadMissing(['employee', 'leaveType']);

        // jouw User model heeft roles() (many-to-many)
        $managers = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'manager'))
            ->get();

        foreach ($managers as $manager) {
            $manager->notify(new LeaveRequestSubmitted($leaveRequest));
        }
    }

    public function updated(LeaveRequest $leaveRequest): void
    {
        if (!$leaveRequest->wasChanged('status')) {
            return;
        }

        if (!in_array($leaveRequest->status, [
            LeaveRequest::STATUS_APPROVED,
            LeaveRequest::STATUS_REJECTED,
            LeaveRequest::STATUS_CANCELED,
        ], true)) {
            return;
        }

        $leaveRequest->loadMissing('employee');

        $email = $leaveRequest->employee?->email;
        if (!$email) {
            return;
        }

        Mail::to($email)->send(new LeaveRequestStatusChanged($leaveRequest));
    }
}
