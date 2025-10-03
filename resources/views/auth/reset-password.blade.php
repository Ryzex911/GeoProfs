<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Wachtwoord resetten — GeoProfs</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Source+Sans+3:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/reset-password.css') }}">
</head>
<body class="page page--auth">
<aside class="visual">
    <div class="visual__overlay"></div>
    <div class="visual__brand">
        <svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true" class="logo">
            <path d="M3 12l6-6 6 6-6 6-6-6z" fill="none" stroke="currentColor" stroke-width="1.5"/>
            <path d="M9 6l6 6" stroke="currentColor" stroke-width="1.5" fill="none"/>
        </svg>
        <span>GeoProfs</span>
    </div>
</aside>

<main class="card">
    <header class="card__header">
        <h1>Wachtwoord resetten</h1>
        <p class="muted">Vul je e-mail in. We sturen je een resetlink.</p>
    </header>

    <form class="form" action="#" method="get" onsubmit="return false;">
        <div class="field">
            <label for="email">E-mailadres</label>
            <input id="email" name="email" type="email" placeholder="naam@bedrijf.nl"/>
            <small class="hint">Je ontvangt binnen enkele minuten een e-mail.</small>
        </div>

        <button class="btn btn--primary" type="button">
            <svg width="18" height="18" viewBox="0 0 24 24" class="btn__icon" aria-hidden="true">
                <path d="M21 12a9 9 0 10-3.51 7.11M21 12l-3-3m3 3l-3 3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Stuur resetlink
        </button>

        <a class="btn btn--ghost" href="{{ route('login') }}">Terug naar inloggen</a>
    </form>

    <footer class="card__footer">
        <p class="muted">Geen mail ontvangen? Controleer je spam of <a class="link" href="#">contacteer support</a>.</p>
    </footer>
</main>
</body>
</html>
