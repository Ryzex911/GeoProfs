<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GeoProfs Dashboard</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --geo-blue: #0E3A5B;
            --geo-green: #3FB950;
            --bg: #F3F4F6;
            --card: #ffffff;
            --radius: 16px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Inter", sans-serif; }

        body {
            background: var(--bg);
            color: #0f172a;
            display: flex;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: var(--geo-blue);
            color: #fff;
            padding: 22px;
            display: flex;
            flex-direction: column;
        }

        .sidebar .logo {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 32px;
        }

        .sidebar a {
            color: #dcefff;
            text-decoration: none;
            padding: 12px 8px;
            display: block;
            border-radius: 8px;
            margin-bottom: 6px;
            transition: .2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255,255,255,0.15);
        }

        /* Main content */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Top bar */
        .topbar {
            background: var(--card);
            padding: 16px 26px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e5e7eb;
        }

        .userbox {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .userbox img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
        }

        /* Cards */
        .content {
            padding: 26px;
        }

        .grid {
            display: grid;
            gap: 22px;
        }

        .grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .card {
            background: var(--card);
            padding: 20px;
            border-radius: var(--radius);
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .card h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .kpi {
            font-size: 32px;
            font-weight: 700;
            color: var(--geo-blue);
        }

        /* Calendar placeholder */
        .calendar {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius);
            height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: .7;
            font-size: 14px;
        }

        /* Button */
        .btn {
            background: var(--geo-blue);
            color: #fff;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            margin-top: 12px;
            transition: 0.2s;
        }
        .btn:hover {
            background: #0b2f49;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .sidebar { display: none; }
            .grid-3 { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>



<!-- Main Section -->
<div class="main">

    <!-- Topbar -->
    <div class="topbar">
        <h2>Welkom, medewerker </h2>

        <div class="userbox">
            <span>Tawfik</span>
            <img src="https://i.pravatar.cc/150?img=12">
        </div>
    </div>

    <!-- Content -->
    <div class="content">

        <!-- KPI Cards -->
        <div class="grid grid-3">
            <div class="card">
                <h3>Verlof saldo</h3>
                <div class="kpi">+200 uur</div>
            </div>

            <div class="card">
                <h3>Lopende aanvragen</h3>
                <div class="kpi">03</div>
            </div>



            <!-- Calendar + button -->
            <div class="grid" style="margin-top:26px;">
                <div class="calendar">Kalender komt hier</div>

                <div class="card">
                    <h3>Verlof aanvragen</h3>
                    <p>Plan je verlofperiode of meld afwezigheid.</p>
                    <form method="GET" action="{{ route('requestdashboard') }}" style="display:inline;">
                        @csrf
                        <button class="btn" id="goToLeave">Nieuw verlof verzoek</button>
                    </form>
                </div>
            </div>
            <div class="card">
                <h3>mijn aanvragen</h3>
                <p>bekijk je aanvragen.</p>
                <button class="btn" id="goToLeave">mijn verlof verzoek</button>
            </div>
            <div class="card">
                <h3>Ziek melden</h3>
                <p>Meld je zelf ziek.</p>
                <button class="btn" id="goToLeave">Melden</button>
            </div>
        </div>

        <form method="post" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button class="btn" id="goToLeave">logout</button>
        </form>
    </div>

</div>
</body>
</html>

