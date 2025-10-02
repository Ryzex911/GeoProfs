<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - GeoProfs</title>
</head>
<body>
<h1>Login</h1>

@if($errors->any())
    <div style="color: red;">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf
    <label>Email:</label>
    <input type="email" name="email" value="{{ old('email') }}" required>
    <br>
    <label>Wachtwoord:</label>
    <input type="password" name="password" required>
    <br>
    <button type="submit">Inloggen</button>
</form>
</body>
</html>
