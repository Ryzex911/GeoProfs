<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document</title>
</head>
<body>
<div>
    <form action={{ route('roles.index') }}>
        <select name="roles" id="roles-select">
            <option value="">Please choose an role</option>

            @foreach($roles as $role)
                <option value={{ $role->id }}>{{ $role->name }}</option>
            @endforeach
        </select>
    </form>
</div>
</body>
</html>
