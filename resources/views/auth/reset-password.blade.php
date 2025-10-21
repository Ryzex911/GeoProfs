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

    <form class="form" method="POST" action="{{ route('password.email') }}">
        @csrf

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @error('email')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <div class="field">
            <label for="email">E-mailadres</label>
            <input id="email" name="email" type="email" placeholder="naam@bedrijf.nl" required />
            <small class="hint">Je ontvangt binnen enkele minuten een e-mail.</small>
        </div>

        <button class="btn btn--primary" type="submit">
            <!-- icon ... -->
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
