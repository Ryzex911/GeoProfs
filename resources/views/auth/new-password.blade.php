<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Nieuw wachtwoord — GeoProfs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/reset-password.css') }}">
</head>
<body class="page page--auth">
<main class="card">
    <header class="card__header">
        <h1>Nieuw wachtwoord instellen</h1>
        <p class="muted">Vul je e-mailadres en een nieuw wachtwoord in.</p>
    </header>

    <form class="form" method="POST" action="{{ route('password.store') }}">
        @csrf

        {{-- required for reset --}}
        <input type="hidden" name="token" value="{{ $token }}"/>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @error('email') <div class="alert alert-danger">{{ $message }}</div> @enderror
        @error('password') <div class="alert alert-danger">{{ $message }}</div> @enderror

        <div class="field">
            <label for="email">E-mailadres</label>
            <input id="email" name="email" type="email"
                   value="{{ old('email', $email) }}" required autocomplete="email"
                   placeholder="naam@bedrijf.nl"/>
        </div>

        <div class="field">
            <label for="password">Nieuw wachtwoord</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"/>
            <small class="hint">Min. 12 tekens, hoofd-/kleine letters, cijfers en symbolen.</small>
        </div>

        <div class="field">
            <label for="password_confirmation">Herhaal wachtwoord</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"/>
        </div>

        <button class="btn btn--primary" type="submit">Wachtwoord opslaan</button>
        <a class="btn btn--ghost" href="{{ route('login') }}">Terug naar inloggen</a>
    </form>
</main>
</body>
</html>
