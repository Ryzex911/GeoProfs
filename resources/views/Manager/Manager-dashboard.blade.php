<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Manager Dashboard — GeoProfs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/manager-dashboard.css') }}">
</head>

<body>
<!-- Topbar -->
<div class="topbar">
    <h2>Welkom, Manager</h2>
    <div class="userbox">
        <span>Tamzid</span>
        <img src="https://i.pravatar.cc/150?img=13" alt="Profiel">
    </div>
</div>

<!-- MAIN -->
<main class="container" role="main">
    <div class="grid grid-3">

        <!-- 1) Verlof aanvragen -->
        <section class="card">
            <div class="card__header">
                <h2 class="card__title">Verlof aanvragen</h2>
            </div>
            <div class="card__body">
                <div class="leave-requests">
                    <!-- Request 1 -->
                    <div class="leave-request-item">
                        <div class="request-header">
                            <img src="https://i.pravatar.cc/150?img=12" alt="Tawfik" class="request-avatar">
                            <div class="request-info">
                                <p class="request-name">Tawfik</p>
                                <p class="request-type">Vakantie</p>
                            </div>
                        </div>
                        <p class="request-dates">15 Jan - 20 Jan 2026</p>
                        <div class="request-actions">
                            <button class="btn btn-approve" title="Goedkeuren">✓</button>
                            <button class="btn btn-reject" title="Afkeuren">✕</button>
                        </div>
                    </div>

                    <!-- Request 2 -->
                    <div class="leave-request-item">
                        <div class="request-header">
                            <img src="https://i.pravatar.cc/150?img=8" alt="Sarah" class="request-avatar">
                            <div class="request-info">
                                <p class="request-name">Sarah</p>
                                <p class="request-type">zwangerschap</p>
                            </div>
                        </div>
                        <p class="request-dates">18 Jan - 19 Jan 2026</p>
                        <div class="request-actions">
                            <button class="btn btn-approve" title="Goedkeuren">✓</button>
                            <button class="btn btn-reject" title="Afkeuren">✕</button>
                        </div>
                    </div>

                    <!-- Request 3 -->
                    <div class="leave-request-item">
                        <div class="request-header">
                            <img src="https://i.pravatar.cc/150?img=15" alt="Marco" class="request-avatar">
                            <div class="request-info">
                                <p class="request-name">Marco</p>
                                <p class="request-type">Anders</p>
                            </div>
                        </div>
                        <p class="request-dates">22 Jan 2026</p>
                        <div class="request-actions">
                            <button class="btn btn-approve" title="Goedkeuren">✓</button>
                            <button class="btn btn-reject" title="Afkeuren">✕</button>
                        </div>
                    </div>

                    <!-- Request 4 -->
                    <div class="leave-request-item">
                        <div class="request-header">
                            <img src="https://i.pravatar.cc/150?img=20" alt="Lisa" class="request-avatar">
                            <div class="request-info">
                                <p class="request-name">Lisa</p>
                                <p class="request-type">Vakantie</p>
                            </div>
                        </div>
                        <p class="request-dates">25 Jan - 30 Jan 2026</p>
                        <div class="request-actions">
                            <button class="btn btn-approve" title="Goedkeuren">✓</button>
                            <button class="btn btn-reject" title="Afkeuren">✕</button>
                        </div>
                    </div>
                </div>

                <a href="#" class="link-all-requests">Bekijk alle aanvragen</a>
            </div>
        </section>

        <!-- 2) Teams overzicht -->
        <section class="card">
            <div class="card__header">
                <h2 class="card__title">Teams overzicht</h2>
                <p class="card__subtitle">bekijk overzicht van verschillende teams</p>
                  <button class="btn btn-primary btn-full" style="margin-top: 10px;">Team overzichten</button>
            </div>
            <div class="card__body">
                <div class="teams-list">
                    <!-- Team 1 -->
                    <div class="team-item">
                        <div class="team-icon">🐬</div>
                        <div class="team-info">
                            <p class="team-name">Team:</p>
                            <p class="team-value">Development</p>
                            <p class="team-name">leden:</p>
                            <p class="team-value">5</p>
                        </div>
                        <div class="team-project">
                            <p class="team-name">project:</p>
                            <p class="team-value">GeoProfs</p>
                        </div>
                    </div>

                    <!-- Team 2 -->
                    <div class="team-item">
                        <div class="team-icon">🐅</div>
                        <div class="team-info">
                            <p class="team-name">Team:</p>
                            <p class="team-value">Design</p>
                            <p class="team-name">leden:</p>
                            <p class="team-value">3</p>
                        </div>
                        <div class="team-project">
                            <p class="team-name">project:</p>
                            <p class="team-value">UI/UX</p>
                        </div>
                    </div>

                    <!-- Team 3 -->
                    <div class="team-item">
                        <div class="team-icon">🦌</div>
                        <div class="team-info">
                            <p class="team-name">Team:</p>
                            <p class="team-value">Marketing</p>
                            <p class="team-name">leden:</p>
                            <p class="team-value">4</p>
                        </div>
                        <div class="team-project">
                            <p class="team-name">project:</p>
                            <p class="team-value">Campaign 2026</p>
                        </div>
                    </div>

                    <!-- Team 4 -->
                    <div class="team-item">
                        <div class="team-icon">🦅</div>
                        <div class="team-info">
                            <p class="team-name">Team:</p>
                            <p class="team-value">Support</p>
                            <p class="team-name">leden:</p>
                            <p class="team-value">2</p>
                        </div>
                        <div class="team-project">
                            <p class="team-name">project:</p>
                            <p class="team-value">Customer Care</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3) Mijn rooster -->
        <section class="card">
            <div class="card__header">
                <h2 class="card__title">Mijn rooster</h2>
                <p class="card__subtitle">voledige rooster overzicht</p>
                <button class="btn btn-primary btn-full" style="margin-top: 10px;">voledige rooster overzicht</button>

            </div>
            <div class="card__body">

                <!-- Kalender Controls -->
                <div class="calendar-controls">
                    <button class="btn-nav" id="prevMonth">←</button>
                    <select id="monthSelect" class="month-select">
                        <option value="0">Januari</option>
                        <option value="1">Februari</option>
                        <option value="2">Maart</option>
                        <option value="3">April</option>
                        <option value="4">Mei</option>
                        <option value="5">Juni</option>
                        <option value="6">Juli</option>
                        <option value="7">Augustus</option>
                        <option value="8">September</option>
                        <option value="9">Oktober</option>
                        <option value="10">November</option>
                        <option value="11">December</option>
                    </select>
                    <select id="yearSelect" class="year-select">
                        <option value="2025">2025</option>
                        <option value="2026" selected>2026</option>
                        <option value="2027">2027</option>
                    </select>
                    <button class="btn-nav" id="nextMonth">→</button>
                </div>

                <!-- Kalender -->
                <div class="calendar">
                    <div class="calendar-header">
                        <div class="day-label">Ma</div>
                        <div class="day-label">Di</div>
                        <div class="day-label">Wo</div>
                        <div class="day-label">Do</div>
                        <div class="day-label">Vr</div>
                        <div class="day-label">Za</div>
                        <div class="day-label">Zo</div>
                    </div>
                    <div class="calendar-grid" id="calendarGrid">
                        <!-- Generated by JS -->
                    </div>
                </div>

                <!-- Tijden voor geselecteerde dag -->
                <div class="schedule-times" id="scheduleTimes" style="display: none;">
                    <h3>Werktijden</h3>
                    <div id="timesList">
                        <!-- Generated -->
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<script>
    // ========== KALENDER LOGIC ==========
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');
    const calendarGrid = document.getElementById('calendarGrid');
    const scheduleTimes = document.getElementById('scheduleTimes');
    const timesList = document.getElementById('timesList');

    let currentDate = new Date(2026, 0, 1); // Januari 2026

    // Mock werkdagen: bijvoorbeeld 2,3,4,5,6 (Ma-Vr)
    const workDays = {
        '2026-01-06': { start: '09:00', end: '17:00' },
        '2026-01-07': { start: '09:00', end: '17:00' },
        '2026-01-08': { start: '09:00', end: '17:00' },
        '2026-01-09': { start: '09:00', end: '17:00' },
        '2026-01-13': { start: '09:00', end: '17:00' },
        '2026-01-14': { start: '09:00', end: '17:00' },
        '2026-01-15': { start: '09:00', end: '17:00' },
        '2026-01-16': { start: '09:00', end: '17:00' },
        '2026-01-20': { start: '09:00', end: '17:00' },
        '2026-01-21': { start: '09:00', end: '17:00' },
        '2026-01-22': { start: '09:00', end: '17:00' },
        '2026-01-23': { start: '09:00', end: '17:00' },
        '2026-01-27': { start: '09:00', end: '17:00' },
        '2026-01-28': { start: '09:00', end: '17:00' },
        '2026-01-29': { start: '09:00', end: '17:00' },
        '2026-01-30': { start: '09:00', end: '17:00' },
    };

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        monthSelect.value = month;
        yearSelect.value = year;

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay() + 1);

        calendarGrid.innerHTML = '';

        let date = new Date(startDate);
        while (date <= lastDay) {
            const dateStr = date.toISOString().split('T')[0];
            const isWorkDay = workDays[dateStr];
            const isCurrentMonth = date.getMonth() === month;

            const dayCell = document.createElement('div');
            dayCell.className = 'calendar-day';

            if (!isCurrentMonth) {
                dayCell.classList.add('other-month');
                dayCell.textContent = '';
            } else {
                dayCell.textContent = date.getDate();

                if (isWorkDay) {
                    dayCell.classList.add('work-day');
                    dayCell.style.cursor = 'pointer';
                    dayCell.addEventListener('click', () => showSchedule(dateStr, isWorkDay));
                } else {
                    dayCell.classList.add('off-day');
                }
            }

            calendarGrid.appendChild(dayCell);
            date.setDate(date.getDate() + 1);
        }
    }

    function showSchedule(dateStr, workDay) {
        if (!workDay) return;

        const times = workDay;
        timesList.innerHTML = `
            <div class="time-block">
                <p><strong>Datum:</strong> ${new Date(dateStr).toLocaleDateString('nl-NL', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
                <p><strong>Tijd:</strong> ${times.start} - ${times.end}</p>
                <p><strong>Duur:</strong> 8 uur</p>
            </div>
        `;
        scheduleTimes.style.display = 'block';
    }

    prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    monthSelect.addEventListener('change', () => {
        currentDate.setMonth(parseInt(monthSelect.value));
        renderCalendar();
    });

    yearSelect.addEventListener('change', () => {
        currentDate.setFullYear(parseInt(yearSelect.value));
        renderCalendar();
    });

    // Initial render
    renderCalendar();
</script>

</body>
</html>
