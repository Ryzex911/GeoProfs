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

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}"/>

        <label>E-mail</label>
        <input name="email" type="email" value="{{ old('email', $email) }}" required />

        <label>Nieuw wachtwoord</label>
        <input name="password" type="password" required />

        <label>Herhaal wachtwoord</label>
        <input name="password_confirmation" type="password" required />

        <button type="submit">Wachtwoord opslaan</button>
    </form>

</main>
</body>
</html>
