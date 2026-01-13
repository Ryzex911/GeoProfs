<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveRequestStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public LeaveRequest $leaveRequest;
    public string $statusLabel;

    public function __construct(LeaveRequest $leaveRequest)
    {
        // Zorg dat relaties beschikbaar zijn
        $this->leaveRequest = $leaveRequest->loadMissing(['employee', 'leaveType']);

        $this->statusLabel = match($this->leaveRequest->status) {
            LeaveRequest::STATUS_PENDING  => 'In afwachting',
            LeaveRequest::STATUS_APPROVED => 'Goedgekeurd',
            LeaveRequest::STATUS_REJECTED => 'Afgekeurd',
            LeaveRequest::STATUS_CANCELED => 'Geannuleerd',
            default => $this->leaveRequest->status,
        };
    }

    public function build()
    {
        return $this->subject('Update over je verlofaanvraag: ' . $this->statusLabel)
            ->markdown('emails.leave_requests.status_changed', [
                'leaveRequest' => $this->leaveRequest,
                'statusLabel'  => $this->statusLabel,
                'opmerking'    => $this->leaveRequest->opmerking,
            ]);
    }

}
