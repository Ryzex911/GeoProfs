<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Nieuw wachtwoord — GeoProfs</title>

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
        <h1>Nieuw wachtwoord instellen</h1>
        <p class="muted">Vul je e-mailadres en je nieuwe wachtwoord in.</p>
    </header>

    <form method="POST" action="{{ route('password.store') }}" class="form-vert">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}"/>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @error('email')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror
        @error('password')
        <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <!-- E-mail -->
        <div class="field">
            <label for="email">E-mailadres</label>
            <input id="email" name="email" type="email"
                   value="{{ old('email', $email) }}"
                   readonly
                   style="background-color: #f5f5f5; color: #555; cursor: not-allowed;"
                   placeholder="naam@bedrijf.nl" required autocomplete="email"/>
        </div>
        <!-- Nieuw wachtwoord -->
        <div class="field">
            <label for="password">Nieuw wachtwoord</label>
            <input id="password" name="password" type="password"
                   required autocomplete="new-password"
                   minlength="12"
                   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{12,}$"
                   placeholder="••••••••••••"/>
        </div>

        <!-- Herhaal wachtwoord -->
        <div class="field">
            <label for="password_confirmation">Herhaal wachtwoord</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   required autocomplete="new-password" placeholder="••••••••••••"/>
        </div>

        <!-- Vereisten -->
        <div class="field">
            <ul class="reqs">
                <li>Je wachtwoord moet minimaal 12 tekens bevatten en minstens</li>
                <li>1 hoofdletter (A–Z)</li>
                <li>1 kleine letter (a–z)</li>
                <li>1 cijfer (0–9)</li>
                <li>1 speciaal teken (bijv. !@#$%^&*)</li>
            </ul>
        </div>

        <!-- Buttons -->
        <button class="btn btn--primary" type="submit">Wachtwoord opslaan</button>
        <a class="btn btn--ghost" href="{{ route('login') }}">Terug naar inloggen</a>
    </form>

    <footer class="card__footer">
        <p class="muted">Problemen? Neem contact op met support.</p>
    </footer>
</main>
</body>
</html>
