<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>GeoProfs</title>

    <link rel="stylesheet" href="/css/layout.css">
    <link rel="stylesheet" href="/css/users.css">
</head>

<body>

<nav class="navbar">
    <div class="logo">GeoProfs</div>

    @auth
        <div class="nav-profile">
            <div class="avatar" onclick="toggleProfileMenu()">
                {{ strtoupper(substr(auth()->user()->name,0,1)) }}
            </div>

            <div id="profileMenu" class="profile-menu hidden">
                <strong>{{ auth()->user()->name }}</strong>
                <a href="#">Profiel bekijken</a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">Uitloggen</button>
                </form>
            </div>
        </div>
    @endauth
</nav>

<main class="content">
    @yield('content')
</main>

<script>
    function toggleProfileMenu() {
        document.getElementById('profileMenu').classList.toggle('hidden');
    }
</script>

@stack('scripts')
</body>
</html>
