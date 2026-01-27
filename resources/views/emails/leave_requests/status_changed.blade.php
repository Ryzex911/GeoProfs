@component('mail::message')
    # Status update: {{ $statusLabel }}

    Hallo {{ $leaveRequest->employee?->name ?? 'medewerker' }},

    Je verlofaanvraag is bijgewerkt. Hieronder vind je het overzicht:

    ---

    **Type verlof:** {{ $leaveRequest->leaveType?->name ?? '—' }}

    **Periode:**
    {{ optional($leaveRequest->start_date)->format('d-m-Y H:i') }} — {{ optional($leaveRequest->end_date)->format('d-m-Y H:i') }}

    **Nieuwe status:** **{{ $statusLabel }}**

    @if(!empty($opmerking))
        ---

        **Opmerking van de manager:**
        {{ $opmerking }}
    @endif

    ---

    Bedankt,
    {{ config('app.name') }}
@endcomponent
