<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>GeoProfs</title>

    <link rel="stylesheet" href="/css/layout.css">
    <link rel="stylesheet" href="/css/users.css">
    @yield('page-css')
</head>

<body>

<nav class="navbar">
    <div class="logo">GeoProfs</div>

    @auth
        Session role id: {{ session('active_role_id') }}

        <div class="nav-profile">
            <!-- Role Switcher -->
            <div class="nav-role">
                <div onclick="toggleRoleMenu()">
                    @php
                        $roleService = app(\App\Services\RoleService::class);
                        $activeRole = $roleService->getActiveRole(auth()->user());
                    @endphp

                    Rol: {{ $activeRole ? $activeRole->name : 'Geen' }}
                </div>
                <div id="roleMenu" class="hidden">
                    @foreach(auth()->user()->roles as $role)
                        <form action="{{ route('role.switch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="role_id" value="{{ $role->id }}">
                            <button type="submit">{{ $role->name }}</button>
                        </form>
                    @endforeach
                </div>
            </div>
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

@stack('scripts')
</body>
</html>

<script>
    function toggleProfileMenu() {
        document.getElementById('profileMenu').classList.toggle('hidden');
    }

    function toggleRoleMenu() {
        document.getElementById('roleMenu').classList.toggle('hidden');
    }
</script>
