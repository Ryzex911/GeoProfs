@component('mail::message')
    # Bevestig je login

    Gebruik deze 2FA-code binnen 10 minuten:

    @component('mail::panel')
        <span style="font-size:24px; letter-spacing:2px; font-weight:700;">
    {{ $code }}
</span>
    @endcomponent

    Als jij dit niet was, kun je deze mail negeren.

    Groeten,
    {{ config('app.name') }}
@endcomponent
