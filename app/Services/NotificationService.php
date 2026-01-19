<?php

namespace App\Services;

use App\Mail\LeaveRequestStatusChanged;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendApproval(LeaveRequest $leaveRequest): void
    {
        Mail::to($leaveRequest->employee->email)->send(new LeaveRequestStatusChanged($leaveRequest));
    }

    public function sendRejection(LeaveRequest $leaveRequest): void
    {
        Mail::to($leaveRequest->employee->email)->send(new LeaveRequestStatusChanged($leaveRequest));
    }
}
