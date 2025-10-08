<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Inloggen — GeoProfs</title>

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Source+Sans+3:wght@400;500&display=swap" rel="stylesheet">
    {{-- CSS vanuit /public/css --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="page page--auth">
<aside class="visual">
    <div class="visual__overlay"></div>
    <div class="visual__brand">
        {{-- Vervang door jouw logo --}}
        <svg width="28" height="28" viewBox="0 0 24 24" aria-hidden="true" class="logo">
            <path d="M3 12l6-6 6 6-6 6-6-6z" fill="none" stroke="currentColor" stroke-width="1.5"/>
            <path d="M9 6l6 6" stroke="currentColor" stroke-width="1.5" fill="none"/>
        </svg>
        <span>GeoProfs</span>
    </div>
</aside>

<main class="card">
    <header class="card__header">
        <h1>Inloggen</h1>
        <p class="muted">Welkom terug. Log in om verder te gaan.</p>
    </header>

    {{-- Puur design (geen actie) --}}
    <form class="form" action="#" method="get" onsubmit="return false;">
        <div class="field">
            <label for="email">E-mailadres</label>
            <input id="email" name="email" type="email" placeholder="naam@bedrijf.nl"/>
            <small class="hint">Gebruik je zakelijke e-mail.</small>
        </div>

        <div class="field">
            <label for="password">Wachtwoord</label>
            <input id="password" name="password" type="password" placeholder="••••••••"/>
            <div class="field__row">
                <label class="checkbox">
                    <input type="checkbox"/>
                    <span>Onthoud mij</span>
                </label>
                <a class="link" href="{{ route('password.request') }}">Wachtwoord vergeten?</a>            </div>
        </div>

        <button class="btn btn--primary" type="button"  >
            <svg width="18" height="18" viewBox="0 0 24 24" class="btn__icon" aria-hidden="true">
                <path d="M3 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>

            </svg>
            <a class="link" href="{{ route('password.request') }}"></a>
            Inloggen

        </button>
    </form>

    <footer class="card__footer">
        <p class="muted">Problemen met inloggen? <a class="link" href="#">Neem contact op</a>.</p>
    </footer>
</main>
</body>
</html>
