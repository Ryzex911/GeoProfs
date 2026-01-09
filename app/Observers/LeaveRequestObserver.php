<?php

namespace App\Observers;

use App\Mail\LeaveRequestStatusChanged;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Mail;

class LeaveRequestObserver
{
    public function updated(LeaveRequest $leaveRequest): void
    {
        // Alleen wanneer status echt veranderd is
        if (!$leaveRequest->wasChanged('status')) {
            return;
        }

        // Alleen mailen bij "eindstatussen" (pas aan als je ook pending wilt mailen)
        if (!in_array($leaveRequest->status, [
            LeaveRequest::STATUS_APPROVED,
            LeaveRequest::STATUS_REJECTED,
            LeaveRequest::STATUS_CANCELED,
        ], true)) {
            return;
        }

        // Zorg dat employee bestaat en email heeft
        $leaveRequest->loadMissing('employee');

        $email = $leaveRequest->employee?->email;
        if (!$email) {
            return;
        }

        Mail::to($email)->send(new LeaveRequestStatusChanged($leaveRequest));
    }
}
