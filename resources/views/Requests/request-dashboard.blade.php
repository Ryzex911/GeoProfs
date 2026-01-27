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
    <h2>Welkom, medewerker</h2>

    <div class="userbox">
        <span>{{ auth()->user()->name ?? '—' }}</span>
        <img src="https://i.pravatar.cc/150?img=12" alt="avatar">
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
                    <p class="small-note">
                        Belangrijk: selecteer het begin en het einddatum van je verlof. Het mag niet in het verleden zijn
                        en vakantie minimaal 1 week van tevoren.
                    </p>
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
                            <option
                                value="{{ $type->id }}"
                                data-requires-proof="{{ $type->requires_proof ? 1 : 0 }}"
                                data-name="{{ strtolower($type->name) }}"
                            >
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="small-note" style="margin-top:6px;">
                        Bij sommige verloftypes is bewijs verplicht (bestand of link).
                    </p>
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

                    <!-- Anders / Overig -->
                    <div id="overigWrap" style="display:none">
                        <label for="overig">Andere reden (optioneel)</label>
                        <input id="overig" class="input" type="text" placeholder="Beschrijf de reden…" />
                    </div>

                    <div>
                        <label for="note">Opmerking (optioneel)</label>
                        <textarea id="note" placeholder="Eventuele toelichting…"></textarea>
                    </div>

                    <!-- ✅ PROOF WRAP (alleen zichtbaar bij requires_proof = 1) -->
                    <div id="proofWrap" style="display:none">
                        <label><strong>Bewijs (verplicht)</strong></label>

                        <div style="margin-top:8px;">
                            <label for="proofFile" class="muted" style="display:block; margin-bottom:6px;">Upload bestand (foto/video/pdf/word/ppt)</label>
                            <input
                                id="proofFile"
                                type="file"
                                class="input"
                                style="padding:10px;"
                                accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.ppt,.pptx,.mp4,.mov,.avi,.mkv"
                            />
                    </div>

                    <div class="summary" id="summary">
                        <b>Samenvatting</b><br/>
                        Reden: <span id="sReason">—</span> •
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
<div class="toast" id="toast" role="status" aria-live="polite">Verlofverzoek is verzonden!</div>

<script>
    const from = document.getElementById('from');
    const to   = document.getElementById('to');

    const leaveTypeSelect = document.getElementById('leaveTypeSelect');
    const submitBtn = document.getElementById('submitBtn');
    const resetBtn  = document.getElementById('resetBtn');

    const toast    = document.getElementById('toast');
    const storeUrl = "{{ route('leave-requests.store') }}";

    const proofWrap = document.getElementById('proofWrap');
    const proofFile = document.getElementById('proofFile');
    const proofUrl  = document.getElementById('proofUrl');

    const overigWrap = document.getElementById('overigWrap');
    const overig     = document.getElementById('overig');
    const note       = document.getElementById('note');

    // Samenvatting
    const sReason = document.getElementById('sReason');
    const sFrom   = document.getElementById('sFrom');
    const sTo     = document.getElementById('sTo');
    const sHours  = document.getElementById('sHours');

    function toInputValue(date) {
        const off = date.getTimezoneOffset();
        const local = new Date(date.getTime() - off * 60000);
        return local.toISOString().slice(0, 16);
    }

    /* ======= PROOF + OVERIG toggle ======= */
    function updateTypeUI(){
        const opt = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
        const requiresProof = opt?.dataset?.requiresProof === '1';
        const typeName = (opt?.dataset?.name || '').toLowerCase();

        // proof wrap
        proofWrap.style.display = requiresProof ? 'block' : 'none';
        if (!requiresProof) {
            // clear proof inputs when not required
            if (proofFile) proofFile.value = '';
            if (proofUrl) proofUrl.value = '';
        }

        // "Anders" -> overig
        overigWrap.style.display = (typeName === 'anders') ? 'block' : 'none';
        if (typeName !== 'anders' && overig) overig.value = '';

        // reason label in summary
        sReason.textContent = opt?.text || '—';
    }

    leaveTypeSelect.addEventListener('change', updateTypeUI);
    updateTypeUI();

    /* ======= KALENDER: MAAND / JAAR ======= */
    const monthLabel = document.getElementById('monthLabel');
    const monthNames = ['Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','September','Oktober','November','December'];

    const today = new Date();
    let currentYear  = today.getFullYear();
    let currentMonth = today.getMonth();

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
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        updateMonthLabel();
        renderRange();
    }

    function goToNextMonth() {
        currentMonth++;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        updateMonthLabel();
        renderRange();
    }

    prevBtn.addEventListener('click', goToPrevMonth);
    nextBtn.addEventListener('click', goToNextMonth);

    /* ======= KALENDER: RANGE ======= */
    const dayButtons = document.querySelectorAll('[data-day]');
    let rangeStart = null;
    let rangeEnd   = null;

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
            btn.classList.remove('is-range-start','is-range-end','is-in-range');

            if (!rangeStart) return;

            const start = new Date(rangeStart);
            const end   = rangeEnd ? new Date(rangeEnd) : start;

            if (+d === +start) btn.classList.add('is-range-start');
            else if (+d === +end) btn.classList.add('is-range-end');
            else if (d > start && d < end) btn.classList.add('is-in-range');
        });
    }

    function updateSummary(){
        sFrom.textContent = from.value ? from.value.replace('T', ' ') : '—';
        sTo.textContent   = to.value ? to.value.replace('T', ' ') : '—';

        // simpele uur berekening
        if (from.value && to.value) {
            const a = new Date(from.value);
            const b = new Date(to.value);
            const diff = Math.max(0, (b - a) / 36e5);
            sHours.textContent = diff.toFixed(0);
        } else {
            sHours.textContent = '0';
        }
    }
    from.addEventListener('change', updateSummary);
    to.addEventListener('change', updateSummary);

    dayButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const d = getDateForButton(btn);

            if (!rangeStart || (rangeStart && rangeEnd) || d < rangeStart) {
                rangeStart = d;
                rangeEnd   = null;
            } else {
                rangeEnd = d;
            }

            const startForInput = setTime(rangeStart, 9);
            from.value = toInputValue(startForInput);

            const endForInput = setTime(rangeEnd ?? rangeStart, 17);
            to.value = toInputValue(endForInput);

            renderRange();
            updateSummary();
        });
    });

    renderRange();
    updateSummary();

    /* ======= SUBMIT (FormData voor file upload) ======= */
    submitBtn.addEventListener('click', async () => {
        const leaveTypeId = leaveTypeSelect.value;
        if (!leaveTypeId) { alert('Kies een verloftype.'); return; }
        if (!from.value || !to.value) { alert('Kies Van/Tot.'); return; }

        const opt = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
        const requiresProof = (opt?.dataset?.requiresProof === '1');

        const noteVal   = document.getElementById('note')?.value?.trim() || '';
        const overigVal = document.getElementById('overig')?.value?.trim() || '';
        const leaveTypeText = opt?.text || 'Verlof';
        const reason = overigVal || noteVal || leaveTypeText;

        const proofFile = document.getElementById('proofFile');
        const proofUrl  = document.getElementById('proofUrl');

        const fileChosen = proofFile?.files?.length ? proofFile.files[0] : null;
        const urlVal = (proofUrl?.value || '').trim();

        if (requiresProof && !fileChosen && !urlVal) {
            alert('Bewijs is verplicht: upload een bestand of plak een link.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Verzenden…';

        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const fd = new FormData();
            fd.append('leave_type_id', leaveTypeId);
            fd.append('from', from.value);
            fd.append('to', to.value);
            fd.append('reason', reason);

            if (fileChosen) fd.append('proof', fileChosen);
            if (urlVal) fd.append('proof_url', urlVal);

            const res = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: fd,
            });

            if (res.ok) {
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 2200);

                // reset
                from.value = '';
                to.value = '';
                document.getElementById('note').value = '';
                if (proofFile) proofFile.value = '';
                if (proofUrl) proofUrl.value = '';
                rangeStart = null; rangeEnd = null; renderRange();
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
        if (note) note.value = '';
        if (overig) overig.value = '';
        if (proofFile) proofFile.value = '';
        if (proofUrl) proofUrl.value = '';

        rangeStart = null;
        rangeEnd = null;
        renderRange();
        updateSummary();
    });
</script>
</body>
</html>
