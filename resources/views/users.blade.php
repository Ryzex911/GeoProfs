<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="/js/user-roles.js"></script>
    <title>Users</title>
</head>

<body style="display: flex; justify-content: center; align-items: center; height: 100vh;">
{{--Stijling moet verplaats worden naar een css bestand--}}
<style>
    #roleModal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }

    #roleModal > div {
        background: white;
        margin: 100px auto;
        padding: 20px;
        max-width: 400px;
        border-radius: 5px;
    }
</style>

@if ($errors->any())
    <div style="color: red; padding: 10px; margin-bottom: 10px;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div style="color: green; padding: 10px; margin-bottom: 10px;">
        {{ session('success') }}
    </div>
@endif

{{--Users tabel--}}
<table>
    <thead>
    <tr>
        <th>Naam</th>
        <th>Email</th>
        <th>Rol(len)</th>
        <th>Actie</th>
    </tr>
    </thead>
    <tbody>
    @foreach($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                {{ $user->roles->pluck('name')->join(', ') ?: 'Geen rol' }}
            </td>
            <td>
                @can('updateRoles', $user)
                <button
                    data-user-id="{{ $user->id }}"
                    data-user-roles='{{ $user->roles->pluck('id')->toJson() }}'
                    id="closeModalBtn">
                    Bewerken
                </button>
                @else
                    <span style="color: gray;">Geen toegang</span>
                @endcan
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{--Modal om rollen te wijzigen--}}
<div id="roleModal" style="display:none;">
    <div>
        <h3>Rol aanpassen</h3>

        <form id="roleForm" method="POST">
            @csrf
            @method('PUT')

            <label for="roleSelect">Selecteer rollen:</label>
            <div>
                @foreach($allRoles as $role)
                    <label>
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}">
                        {{ $role->name }}
                    </label>
                @endforeach
            </div>

            <button type="submit">Opslaan</button>
            <button type="button" onclick="closeModal()">Sluiten</button>
        </form>
    </div>
</div>
</body>
</html>
