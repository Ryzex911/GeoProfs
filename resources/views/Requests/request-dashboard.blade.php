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
                    <button class="btn btn-primary" id="submitBtn" disabled>Verzoek indienen</button>
                    <button class="btn btn-secondary" id="resetBtn">Annuleren</button>
                </div>
            </div>
        </section>

        <!-- 3) Saldo -->
        <aside class="card" aria-labelledby="sec-saldo">
            <div class="card__header"><h2 id="sec-saldo" class="card__title">Verlof saldo</h2></div>
            <div class="card__body">
                <div class="kpi">Saldo: <b>+200 uren</b></div>
            </div>
        </aside>
    </div>
</main>

<!-- Toast -->
<div class="toast" id="toast" role="status" aria-live="polite"> Verlofverzoek is verzonden!</div>

<script>
    /* ========== REDEN TILES ========== */
    const tiles = document.querySelectorAll('.tile');
    const sReason = document.getElementById('sReason');
    const overigWrap = document.getElementById('overigWrap');

    tiles.forEach(t => {
        t.addEventListener('click', () => {
            tiles.forEach(x => {
                x.classList.remove('is-selected');
                x.setAttribute('aria-selected','false');
            });
            t.classList.add('is-selected');
            t.setAttribute('aria-selected','true');

            const reason = t.dataset.reason;
            sReason.textContent = reason.charAt(0).toUpperCase()+reason.slice(1);
            overigWrap.style.display = (reason === 'overig') ? 'block' : 'none';
            validate();
        });
    });

    /* ========== DATUM / VALIDATIE ========== */
    const from    = document.getElementById('from');
    const to      = document.getElementById('to');
    const sFrom   = document.getElementById('sFrom');
    const sTo     = document.getElementById('sTo');
    const sHours  = document.getElementById('sHours');
    const submitBtn = document.getElementById('submitBtn');

    // echte "nu"
    const today = new Date();

    // minimaal 7 dagen van tevoren
    const MIN_DAGEN_VAN_TE_VOREN = 7;
    const minDate = new Date(today.getTime());
    minDate.setDate(minDate.getDate() + MIN_DAGEN_VAN_TE_VOREN);

    function toInputValue(date) {
        // lokale tijd omzetten naar value voor datetime-local
        const off = date.getTimezoneOffset();
        const local = new Date(date.getTime() - off * 60000);
        return local.toISOString().slice(0, 16);
    }

    const minStr = toInputValue(minDate);
    from.min = minStr;
    to.min   = minStr;

    function hoursBetween(a,b){
        const start = new Date(a), end = new Date(b);
        const ms = end - start;
        if (isNaN(ms) || ms <= 0) return 0;
        return +(ms / 36e5).toFixed(2);
    }

    function validate(){
        let valid = true;
        const fromDate = from.value ? new Date(from.value) : null;
        const toDate   = to.value   ? new Date(to.value)   : null;

        from.classList.remove('has-error');
        to.classList.remove('has-error');

        sFrom.textContent = fromDate ? fromDate.toLocaleString() : '—';
        sTo.textContent   = toDate   ? toDate.toLocaleString()   : '—';

        // 1) minimaal 1 week van tevoren
        if (fromDate && fromDate < minDate) {
            valid = false;
            from.classList.add('has-error');
        }
        if (toDate && toDate < minDate) {
            valid = false;
            to.classList.add('has-error');
        }

        // 2) tot mag niet vóór van
        if (fromDate && toDate && toDate <= fromDate) {
            valid = false;
            to.classList.add('has-error');
        }

        const h = (fromDate && toDate && valid) ? hoursBetween(from.value, to.value) : 0;
        sHours.textContent = h;

        // Button is alleen enabled als: datums OK + verloftype gekozen
        const leaveTypeSelect = document.getElementById('leaveTypeSelect');
        const hasType = leaveTypeSelect.value !== '';
        submitBtn.disabled = !(valid && h > 0 && hasType);
    }

    from.addEventListener('change', () => {
        if (from.value) {
            to.min = from.value;
            if (to.value && to.value < from.value) {
                to.value = from.value;
            }
        } else {
            to.min = minStr;
        }
        validate();
        renderRange();
    });

    to.addEventListener('change', () => {
        validate();
        renderRange();
    });

    // Valideer als verloftype wijzigt
    document.getElementById('leaveTypeSelect').addEventListener('change', validate);

    /* ========== KALENDER: MAAND / JAAR ========== */

    const monthLabel = document.getElementById('monthLabel');
    const monthNames = [
        'Januari','Februari','Maart','April','Mei','Juni',
        'Juli','Augustus','September','Oktober','November','December'
    ];

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
            btn.classList.remove('is-disabled','is-range-start','is-range-end','is-in-range');

            // disable oude datums
            if (d < minDate) {
                btn.classList.add('is-disabled');
                return;
            }

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

            if (d < minDate) {
                alert('Je kunt geen verlof in het verleden of minder dan 1 week van tevoren aanvragen.');
                return;
            }

            // Als nog geen start, of er is al start+end, of klik is vóór huidige start → nieuwe range starten
            if (!rangeStart || (rangeStart && rangeEnd) || d < rangeStart) {
                rangeStart = d;
                rangeEnd   = null;
            } else {
                // tweede klik → eind van de range
                rangeEnd = d;
            }

            // Van/Tot invullen
            const startForInput = setTime(rangeStart, 9);  // 09:00
            from.value = toInputValue(startForInput);

            let endForInput;
            if (rangeEnd) {
                endForInput = setTime(rangeEnd, 17);       // 17:00
            } else {
                endForInput = setTime(rangeStart, 17);     // 17:00 zelfde dag
            }
            to.value = toInputValue(endForInput);

            validate();
            renderRange();
        });
    });

   /* ========== SUBMIT / RESET (AJAX) ========== */
const toast = document.getElementById('toast');
const storeUrl = "{{ route('leave-requests.store') }}"; // Laravel route

document.getElementById('submitBtn').addEventListener('click', async () => {
    // Haal verloftype ID uit selectbox
    const leaveTypeSelect = document.getElementById('leaveTypeSelect');
    const leaveTypeId = leaveTypeSelect.value;

    // Controleer dat type is gekozen
    if (!leaveTypeId) {
        alert('Kies alstublieft een verloftype.');
        return;
    }

    const fromVal = from.value; // datetime-local: 'YYYY-MM-DDTHH:MM'
    const toVal = to.value;
    const reason = document.getElementById('overig').value || null;

    // Basis feedback
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
                leave_type_id: parseInt(leaveTypeId),
                from: fromVal,
                to: toVal,
                reason: reason
            })
        });

        if (res.status === 201 || res.ok) {
            // success
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 2200);

            // optioneel: reset form / range
            from.value = "";
            to.value = "";
            document.getElementById('overig').value = "";
            rangeStart = null;
            rangeEnd = null;
            validate();
            renderRange();
        } else if (res.status === 422) {
            const payload = await res.json();
            const errors = payload.errors || payload;
            // Toon eenvoudige alerts (je kunt dit uitbreiden naar per-field UI)
            alert('Validatie fouten: ' + JSON.stringify(errors));
        } else {
            const payload = await res.json().catch(()=>null);
            alert('Fout bij opslaan: ' + (payload?.message || res.statusText));
        }
    } catch (err) {
        console.error(err);
        alert('Er is een fout opgetreden bij het versturen.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Verzoek indienen';
    }
});

// reset blijft hetzelfde (optioneel)
document.getElementById('resetBtn').addEventListener('click', ()=>{
    from.value = "";
    to.value   = "";
    document.getElementById('overig').value = "";
    rangeStart = null;
    rangeEnd   = null;
    validate();
    renderRange();
});

    // eerste run
    validate();
    renderRange();
</script>


</body>
</html>
