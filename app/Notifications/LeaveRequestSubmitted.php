<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Als queued => model wordt opnieuw opgehaald, dus relaties opnieuw laden:
        $this->leaveRequest->loadMissing(['employee', 'leaveType']);

        $employeeName = $this->leaveRequest->employee?->name ?? 'Medewerker';
        $typeName     = $this->leaveRequest->leaveType?->name ?? 'Verlof';
        $start        = optional($this->leaveRequest->start_date)->format('d-m-Y H:i');
        $end          = optional($this->leaveRequest->end_date)->format('d-m-Y H:i');

        return (new MailMessage)
            ->subject('Nieuwe verlofaanvraag')
            ->line("Er is een nieuwe verlofaanvraag ingediend door: {$employeeName}.")
            ->line("Type: {$typeName}")
            ->line("Periode: {$start} t/m {$end}")
            ->action('Open aanvragen', route('manager.requests.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'employee_name' => $this->leaveRequest->employee?->name,
            'type' => $this->leaveRequest->leaveType?->name,
            'start' => optional($this->leaveRequest->start_date)->toDateTimeString(),
            'end'   => optional($this->leaveRequest->end_date)->toDateTimeString(),
            'hours' => $this->leaveRequest->hours ?? null,
            'url'   => route('manager.requests.index'),
        ];
    }
}
