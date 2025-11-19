<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document</title>
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh;">
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
                <button class="open-modal" data-user="{{ $user }}">Bewerken</button>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
