<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Verlof aanvragen — GeoProfs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/request-dashboard.css') }}">


</head>

<body>
    <div style="max-width: 900px; margin: 40px auto; font-family: sans-serif;">
        <h1 style="margin-bottom: 20px;">Leave Requests (Demo)</h1>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
            <tr style="background: #eee;">
                <th style="padding: 8px; border: 1px solid #ddd;">Employee</th>
                <th style="padding: 8px; border: 1px solid #ddd;">Type</th>
                <th style="padding: 8px; border: 1px;">Period</th>
                <th style="padding: 8px; border: 1px solid #ddd;">Status</th>
                <th style="padding: 8px; border: 1px solid #ddd;">Reason</th>
                <th style="padding: 8px; border: 1px solid #ddd;">Actions</th>
            </tr>
            </thead>

            <tbody>
            @foreach ($requests as $req)
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ $req->employee_id }}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ ucfirst($req->type) }}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        {{ $req->start_date }} → {{ $req->end_date }}
                    </td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ ucfirst($req->status) }}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">{{ $req->reason }}</td>
                    <td style="padding: 8px; border: 1px solid #ddd;">
                        <button style="padding: 5px 10px; cursor: pointer;">View</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</body>
