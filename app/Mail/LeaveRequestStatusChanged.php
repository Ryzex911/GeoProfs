<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveRequestStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LeaveRequest $leaveRequest)
    {
        //
    }

    public function build()
    {
        $statusLabel = match ($this->leaveRequest->status) {
            LeaveRequest::STATUS_APPROVED => 'Goedgekeurd',
            LeaveRequest::STATUS_REJECTED => 'Afgekeurd',
            LeaveRequest::STATUS_CANCELED => 'Geannuleerd',
            default => ucfirst((string) $this->leaveRequest->status),
        };

        // als je kolom anders heet, pas hier aan (bv: remark, comment, notes)
        $opmerking = $this->leaveRequest->opmerking ?? null;

        return $this->subject('Update over je verlofaanvraag: ' . $statusLabel)
            ->markdown('emails.leave_requests.status_changed', [
                'leaveRequest' => $this->leaveRequest,
                'statusLabel'  => $statusLabel,
                'opmerking'    => $opmerking,
            ]);
    }
}
