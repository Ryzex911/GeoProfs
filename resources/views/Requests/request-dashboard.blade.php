<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Verlof aanvragen — GeoProfs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/request-dashboard.css') }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

</head>

<body>
<!-- Topbar -->
<div class="topbar">
    <h2>Welkom, medewerker </h2>

    <div class="userbox">
        <span>Tawfik</span>
        <img src="https://i.pravatar.cc/150?img=12">
    </div>
</div>
<!-- MAIN -->
<main class="container" role="main">
    <div class="grid grid-3-2" style="margin-top:22px;">
        <!-- 1) Kalender -->
        <section class="card" aria-labelledby="sec-datum">
            <div class="card__header"><h2 id="sec-datum" class="card__title">Selecteer een datum</h2></div>
            <div class="card__body">
                <div class="calendar" aria-label="Kalender (statisch voorbeeld)">
                    <div class="cal-head">
                        <div class="cal-title" id="monthLabel">Januari 2022</div>
                        <div class="cal-actions">
                            <button class="cal-btn" aria-disabled="true">‹</button>
                            <button class="cal-btn" aria-disabled="true">›</button>
                        </div>
                    </div>
                    <div class="cal-grid" role="grid" aria-readonly="true">
                        <div class="dow">MA</div><div class="dow">DI</div><div class="dow">WO</div><div class="dow">DO</div><div class="dow">VR</div><div class="dow">ZA</div><div class="dow">ZO</div>
                        <span></span><span></span><span></span><span></span>
                        <!-- Days -->
                        <button data-day="1">1</button><button data-day="2">2</button><button data-day="3">3</button>
                        <button data-day="4">4</button><button data-day="5">5</button><button data-day="6">6</button><button data-day="7">7</button>
                        <button data-day="8">8</button><button data-day="9">9</button><button data-day="10">10</button><button data-day="11">11</button>
                        <button class="is-today" data-day="12">12</button><button data-day="13">13</button><button data-day="14">14</button>
                        <button data-day="15">15</button><button data-day="16">16</button><button data-day="17">17</button><button data-day="18">18</button>
                        <button data-day="19">19</button><button data-day="20">20</button><button data-day="21">21</button><button data-day="22">22</button>
                        <button data-day="23">23</button><button data-day="24">24</button><button data-day="25">25</button><button data-day="26">26</button>
                        <button data-day="27">27</button><button data-day="28">28</button><button data-day="29">29</button><button data-day="30">30</button><button data-day="31">31</button>
                    </div>
                    <p class="small-note">Belangrijk: selecteer het begin en het einddatum van je verlof het mag niet in her verleden
                    zijn of telaat { minimaal 1 week van te voren} </p>
                </div>
            </div>
        </section>

        <!-- 2) Reden + velden -->
        <section class="card" aria-labelledby="sec-reden">
            <div class="card__header"><h2 id="sec-reden" class="card__title">Verlof reden</h2></div>
            <div class="card__body">
                <!-- Verloftype selectbox -->
                <div style="margin-bottom:16px;">
                    <label for="leaveTypeSelect"><strong>Verloftype *</strong></label>
                    <select id="leaveTypeSelect" class="input" required style="margin-top:6px;">
                        <option value="">Kies een verloftype</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="formgrid" style="margin-top:14px">
                    <div>
                        <label for="from">Van</label>
                        <input id="from" class="input" type="datetime-local" />
                    </div>

                    <div>
                        <label for="to">Tot</label>
                        <input id="to" class="input" type="datetime-local" />
                        </div>

                    <div id="overigWrap" style="display:none">
                        <label for="overig">Andere reden (optioneel)</label>
                        <input id="overig" class="input" type="text" placeholder="Beschrijf de reden…" />
                    </div>

                    <div>
                        <label for="note">Opmerking (optioneel)</label>
                        <textarea id="note" placeholder="Eventuele toelichting…"></textarea>
                    </div>

                    <div class="summary" id="summary">
                        <b>Samenvatting</b><br/>
                        Reden: <span id="sReason">Verlof</span> •
                        Van: <span id="sFrom">—</span> •
                        Tot: <span id="sTo">—</span> •
                        Duur: <span id="sHours">0</span> uur
                    </div>
                </div>

                <div class="actions" style="margin-top:16px">
                    <button class="btn btn-primary" id="submitBtn">Verzoek indienen</button>
                    <button class="btn btn-secondary" id="resetBtn">Annuleren</button>
                </div>
            </div>
        </section>

        <!-- 3) Verlodsaldo -->
        <aside class="card" aria-labelledby="sec-saldo">
            <div class="card__header"><h2 id="sec-saldo" class="card__title">Mijn verlofsaldo</h2></div>
            <div class="card__body">
                <!-- Saldo KPI (groot getal) -->
                <div class="kpi" style="text-align: center; margin-bottom: 24px;">
                    <div style="font-size: 0.85rem; color: #666; margin-bottom: 8px;">Resterend</div>
                    <div style="font-size: 2.5rem; font-weight: 700; color: #2563eb;">
                        {{ number_format($balance['remaining_days'], 1) }} <span style="font-size: 1.2rem;">dagen</span>
                    </div>
                    <div style="font-size: 1rem; color: #666; margin-top: 4px;">
                        ({{ number_format($balance['remaining_hours'], 1) }} uur)
                    </div>
                </div>

                <!-- Detailbrekking -->
                <div style="background: #f9fafb; padding: 12px; border-radius: 6px; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: #666;">Startsaldo:</span>
                        <strong>{{ number_format($balance['start_days'], 1) }} dagen</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: #666;">Gebruikt:</span>
                        <strong>{{ number_format($balance['used_days'], 1) }} dagen</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #666;">Resterend:</span>
                        <strong style="color: #2563eb;">{{ number_format($balance['remaining_days'], 1) }} dagen</strong>
                    </div>
                </div>

                <!-- Waarschuwing (indien nodig) -->
                @if($balance['remaining_days'] < 3)
                <div style="margin-top: 12px; padding: 10px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px; font-size: 0.85rem; color: #92400e;">
                    ⚠️ Je saldo is laag. Plan je verlof voorzichtig!
                </div>
                @endif

                <p style="font-size: 0.75rem; color: #9ca3af; margin-top: 12px; margin-bottom: 0;">
                    Gegevens voor {{ $balance['year'] }}
                </p>
            </div>
        </aside>
    </div>
</main>

<!-- Toast -->
<div class="toast" id="toast" role="status" aria-live="polite"> Verlofverzoek is verzonden!</div>

<script>
    /* =========================================================
       EINDSCRIPT:
       - Kalender (range highlight) + auto invullen from/to
       - Submit (AJAX) + Reset
       - Geen 1-week validatie
       - Reason altijd meesturen (backend vereist reason)
       ========================================================= */

    const from = document.getElementById('from');
    const to   = document.getElementById('to');

    const leaveTypeSelect = document.getElementById('leaveTypeSelect');
    const submitBtn = document.getElementById('submitBtn');
    const resetBtn  = document.getElementById('resetBtn');

    const toast    = document.getElementById('toast');
    const storeUrl = "{{ route('leave-requests.store') }}";

    // Helper: zet Date om naar datetime-local string (YYYY-MM-DDTHH:MM)
    function toInputValue(date) {
        const off = date.getTimezoneOffset();
        const local = new Date(date.getTime() - off * 60000);
        return local.toISOString().slice(0, 16);
    }

    /* ========== KALENDER: MAAND / JAAR ========== */
    const monthLabel = document.getElementById('monthLabel');
    const monthNames = [
        'Januari','Februari','Maart','April','Mei','Juni',
        'Juli','Augustus','September','Oktober','November','December'
    ];

    const today = new Date();
    let currentYear  = today.getFullYear();
    let currentMonth = today.getMonth(); // 0–11

    function updateMonthLabel() {
        monthLabel.textContent = `${monthNames[currentMonth]} ${currentYear}`;
    }
    updateMonthLabel();

    const prevBtn = document.querySelector('.cal-actions button:first-child');
    const nextBtn = document.querySelector('.cal-actions button:last-child');

    prevBtn.style.cursor = 'pointer';
    nextBtn.style.cursor = 'pointer';
    prevBtn.removeAttribute('aria-disabled');
    nextBtn.removeAttribute('aria-disabled');

    function goToPrevMonth() {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        updateMonthLabel();
        renderRange();
    }

    function goToNextMonth() {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        updateMonthLabel();
        renderRange();
    }

    prevBtn.addEventListener('click', goToPrevMonth);
    nextBtn.addEventListener('click', goToNextMonth);

    /* ========== KALENDER: RANGE-SELECT ========== */
    const dayButtons = document.querySelectorAll('[data-day]');
    let rangeStart = null; // Date
    let rangeEnd   = null; // Date

    function getDateForButton(btn) {
        const day = btn.dataset.day.padStart(2, '0');
        const monthStr = String(currentMonth + 1).padStart(2, '0');
        return new Date(`${currentYear}-${monthStr}-${day}T00:00:00`);
    }

    function setTime(date, hours) {
        const d = new Date(date.getTime());
        d.setHours(hours, 0, 0, 0);
        return d;
    }

    function renderRange() {
        dayButtons.forEach(btn => {
            const d = getDateForButton(btn);

            // reset classes
            btn.classList.remove('is-range-start','is-range-end','is-in-range');

            if (!rangeStart) return;

            const start = new Date(rangeStart);
            const end   = rangeEnd ? new Date(rangeEnd) : start;

            if (+d === +start) {
                btn.classList.add('is-range-start');
            } else if (+d === +end) {
                btn.classList.add('is-range-end');
            } else if (d > start && d < end) {
                btn.classList.add('is-in-range');
            }
        });
    }

    dayButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const d = getDateForButton(btn);

            // start/end selection
            if (!rangeStart || (rangeStart && rangeEnd) || d < rangeStart) {
                rangeStart = d;
                rangeEnd   = null;
            } else {
                rangeEnd = d;
            }

            // inputs invullen
            const startForInput = setTime(rangeStart, 9);  // 09:00
            from.value = toInputValue(startForInput);

            const endForInput = setTime(rangeEnd ?? rangeStart, 17); // 17:00
            to.value = toInputValue(endForInput);

            renderRange();
        });
    });

    renderRange();

    /* ========== SUBMIT (AJAX) + RESET ========== */
    submitBtn.addEventListener('click', async () => {
        const leaveTypeId = leaveTypeSelect.value;

        // Backend eist reason -> stuur altijd iets
        const leaveTypeText = leaveTypeSelect.options[leaveTypeSelect.selectedIndex]?.text || 'Verlof';
        const noteVal = document.getElementById('note')?.value?.trim() || '';
        const overigVal = document.getElementById('overig')?.value?.trim() || '';

        const reason = overigVal || noteVal || leaveTypeText;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Verzenden…';

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const res = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    leave_type_id: parseInt(leaveTypeId, 10),
                    from: from.value,
                    to: to.value,
                    reason: reason,
                }),
            });

            if (res.ok) {
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 2200);

                // reset alles
                from.value = '';
                to.value = '';
                const note = document.getElementById('note');
                if (note) note.value = '';
                const overig = document.getElementById('overig');
                if (overig) overig.value = '';

                rangeStart = null;
                rangeEnd = null;
                renderRange();
            } else if (res.status === 422) {
                const payload = await res.json().catch(() => null);
                alert(payload?.message || 'Validatie fout (422).');
            } else {
                const payload = await res.json().catch(() => null);
                alert(payload?.message || 'Opslaan mislukt.');
            }
        } catch (e) {
            console.error(e);
            alert('Fout bij versturen.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Verzoek indienen';
        }
    });

    resetBtn.addEventListener('click', () => {
        from.value = '';
        to.value = '';
        const note = document.getElementById('note');
        if (note) note.value = '';
        const overig = document.getElementById('overig');
        if (overig) overig.value = '';

        rangeStart = null;
        rangeEnd = null;
        renderRange();
    });


</script>




</body>
</html>
