<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="/js/user-roles.js" defer></script>
    <title>Users</title>
</head>

<body style="display: flex; justify-content: center; align-items: center; height: 100vh;">
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
                <button
                    onclick="openModal({{ $user->id }}, {{ $user->roles->pluck('id') }})"
                    data-user-id="{{ $user->id }}"
                    data-user-roles="{{ $user->roles->pluck('id') }}"
                    class="open-modal">Bewerken
                </button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div id="roleModal" style="display:none;">
    <div>
        <h3>Rol aanpassen</h3>

        <form id="roleForm" method="POST">
            @csrf
            @method('PUT')

            <select name="roles[]" id="roleSelect" multiple size="5">
                @foreach($allRoles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>

            <button type="submit">Opslaan</button>
            <button type="button" onclick="closeModal()">Sluiten</button>
        </form>
    </div>
</div>
</body>
</html>
