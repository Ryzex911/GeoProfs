<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — GeoProfs</title>
    <style>
        body {
            font-family: Inter, system-ui, sans-serif;
            background: #f3f4f6;
            color: #0e3a5b;
            text-align: center;
            padding-top: 80px;
        }
        h1 { font-size: 28px; margin-bottom: 10px; }
        p { color: #555; }
        a {
            display: inline-block;
            margin-top: 20px;
            color: #fff;
            background: #0e3a5b;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover { background: #164b74; }
    </style>
</head>
<body>
<form method="POST" action="{{ route('logout') }}" style="display:inline;">
    @csrf
    <button type="submit">Uitloggen</button>
</form>
</body>
</html>
