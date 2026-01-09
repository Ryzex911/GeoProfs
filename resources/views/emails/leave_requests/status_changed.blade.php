@component('mail::message')
    # Status update: {{ $statusLabel }}

    Hallo {{ $leaveRequest->employee?->name ?? 'medewerker' }},

    De status van je verlofaanvraag is aangepast.

    **Type:** {{ $leaveRequest->leaveType?->name ?? '-' }}
    **Periode:** {{ optional($leaveRequest->start_date)->format('d-m-Y H:i') }} — {{ optional($leaveRequest->end_date)->format('d-m-Y H:i') }}
    **Nieuwe status:** {{ $statusLabel }}

    @if($leaveRequest->reason)
        **Opmerking/reden:** {{ $leaveRequest->reason }}
    @endif

    Bedankt,
    {{ config('app.name') }}
@endcomponent
