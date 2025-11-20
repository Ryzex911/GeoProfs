<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Verlof aanvragen — GeoProfs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root{
            --geo-blue:#0E3A5B; --geo-green:#3FB950; --bg:#F3F4F6; --text:#0F172A;
            --muted:#E5E7EB; --card:#FFFFFF; --radius:16px; --shadow:0 12px 30px rgba(0,0,0,.06);
        }

        /* Base */
        *{box-sizing:border-box}
        html,body{height:100%}
        body{margin:0;font-family:Inter,system-ui,Arial,sans-serif;background:var(--bg);color:var(--text)}
        .container{max-width:1140px;margin:0 auto;padding:24px}

        /* Hero */
        .hero{
            background: radial-gradient(140% 120% at 10% 0%, #164C75 0%, #0E3A5B 60%) no-repeat;
            color:#fff; padding:28px 0 34px; position:relative;
            box-shadow: inset 0 -1px 0 rgba(255,255,255,.15);
        }
        .hero__header{display:flex;align-items:center;justify-content:space-between;gap:16px}
        .hero__title{font-size:28px;font-weight:700;margin:0}
        .hero__actions{display:flex;align-items:center;gap:10px}
        .btn-ghost{background:transparent;color:#fff;border:1px solid rgba(255,255,255,.5);padding:8px 12px;border-radius:12px;cursor:pointer}
        .btn-ghost:hover{background:rgba(255,255,255,.12)}
        .pill{width:44px;height:24px;border-radius:999px;background:rgba(255,255,255,.25);position:relative}
        .pill::after{content:"";position:absolute;inset:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;opacity:.9}

        /* Grid */
        .grid{display:grid;gap:24px}
        .grid-3-2{grid-template-columns:2fr 1.4fr 1fr}
        @media (max-width:1100px){.grid-3-2{grid-template-columns:1fr 1fr}}
        @media (max-width:800px){.grid-3-2{grid-template-columns:1fr}}

        /* Card */
        .card{background:var(--card);border:1px solid var(--muted);border-radius:var(--radius);box-shadow:var(--shadow)}
        .card__header{padding:18px 20px;border-bottom:1px solid var(--muted)}
        .card__title{margin:0;font-weight:600}
        .card__body{padding:20px}

        /* Calendar (placeholder, toegankelijk) */
        .calendar{display:grid;gap:10px}
        .cal-head{display:flex;align-items:center;justify-content:space-between}
        .cal-title{font-weight:600}
        .cal-actions{display:flex;gap:6px}
        .cal-btn{border:1px solid var(--muted);background:#fff;border-radius:10px;padding:8px 10px;cursor:not-allowed;opacity:.6}
        .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:8px}
        .cal-grid .dow{font-size:12px;opacity:.7;text-align:center}
        .cal-grid button{height:40px;border:1px solid #E5E7EB;background:#fff;border-radius:10px;cursor:pointer}
        .cal-grid button[disabled]{cursor:not-allowed;opacity:.65}
        .cal-grid .is-today{border-color:var(--geo-blue);box-shadow:0 0 0 2px rgba(14,58,91,.15)}

        /* Tiles/reden */
        .tiles{display:grid;gap:12px}
        .tile{display:flex;flex-direction:column;gap:6px;padding:14px;border:1px solid var(--muted);border-radius:12px;background:#FAFAFA;cursor:pointer;transition:.15s}
        .tile:hover{transform:translateY(-1px)}
        .tile.is-selected{border-color:var(--geo-blue);box-shadow:0 0 0 3px rgba(14,58,91,.12)}
        .tile__title{font-weight:600}
        .small-note{font-size:12px;opacity:.7}

        /* Inputs */
        .formgrid{display:grid;gap:14px}
        label{font-weight:500}
        .input, textarea, select{width:100%;padding:12px 13px;border:1px solid var(--muted);border-radius:12px;background:#fff;font:inherit}
        textarea{min-height:96px;resize:vertical}
        .help{font-size:12px;opacity:.75}

        /* Saldo KPI */
        .kpi{
            display:flex;align-items:center;justify-content:center;
            height:120px;background:#F7FAF9;border:1px solid #DCFCE7;border-radius:14px;
        }
        .kpi b{color:#15803D;font-size:28px}

        /* Actions */
        .actions{display:flex;gap:12px;flex-wrap:wrap}
        .btn{padding:12px 16px;border-radius:12px;border:0;cursor:pointer;font-weight:600}
        .btn-primary{background:var(--geo-blue);color:#fff}
        .btn-secondary{background:#fff;color:var(--geo-blue);border:1px solid var(--geo-blue)}
        .btn:disabled{opacity:.6;cursor:not-allowed}

        /* Summary */
        .summary{background:#FCFDFE;border:1px dashed #C7D2FE;border-radius:14px;padding:14px;font-size:14px}

        /* Toast */
        .toast{
            position:fixed;right:22px;bottom:22px;background:#0F766E;color:#fff;
            padding:12px 16px;border-radius:12px;box-shadow:var(--shadow);transform:translateY(20px);opacity:0;pointer-events:none;transition:.2s
        }
        .toast.show{transform:translateY(0);opacity:1}

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

    </style>
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
                <div class="tiles" role="tablist" aria-label="Reden">
                    <button class="tile is-selected" role="tab" aria-selected="true" data-reason="verlof">
                        <span class="tile__title">Verlof</span>
                        <span class="small-note">Betaald verlof / vrij</span>
                    </button>
                    <button class="tile" role="tab" aria-selected="false" data-reason="ziekte">
                        <span class="tile__title">Ziekte</span>
                        <span class="small-note">Ziekmelding</span>
                    </button>
                    <button class="tile" role="tab" aria-selected="false" data-reason="overig">
                        <span class="tile__title">Overig</span>
                        <span class="small-note">Bijzonder/verlof anders</span>
                    </button>
                </div>

                <div class="formgrid" style="margin-top:14px">
                    <div>
                        <label for="from">Van</label>
                        <input id="from" class="input" type="datetime-local" />
                        <div class="help">Bijv. 2025-01-12 09:00</div>
                    </div>

                    <div>
                        <label for="to">Tot</label>
                        <input id="to" class="input" type="datetime-local" />
                        <div class="help">Bijv. 2025-01-12 17:00</div>
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
    /* Reden tiles */
    const tiles = document.querySelectorAll('.tile');
    const sReason = document.getElementById('sReason');
    const overigWrap = document.getElementById('overigWrap');

    tiles.forEach(t => {
        t.addEventListener('click', () => {
            tiles.forEach(x => { x.classList.remove('is-selected'); x.setAttribute('aria-selected','false'); });
            t.classList.add('is-selected'); t.setAttribute('aria-selected','true');
            const reason = t.dataset.reason;
            sReason.textContent = reason.charAt(0).toUpperCase()+reason.slice(1);
            overigWrap.style.display = (reason === 'overig') ? 'block' : 'none';
            validate();
        });
    });

    /* Samenvatting + validatie */
    const from = document.getElementById('from');
    const to = document.getElementById('to');
    const sFrom = document.getElementById('sFrom');
    const sTo = document.getElementById('sTo');
    const sHours = document.getElementById('sHours');
    const submitBtn = document.getElementById('submitBtn');

    function hoursBetween(a,b){
        const start = new Date(a), end = new Date(b);
        const ms = end - start;
        if (isNaN(ms) || ms <= 0) return 0;
        return +(ms / 36e5).toFixed(2); // uren met 2 decimalen
    }

    function validate(){
        sFrom.textContent = from.value ? new Date(from.value).toLocaleString() : '—';
        sTo.textContent   = to.value ? new Date(to.value).toLocaleString() : '—';
        const h = hoursBetween(from.value, to.value);
        sHours.textContent = h;
        submitBtn.disabled = !(h > 0);
    }
    from.addEventListener('change', validate);
    to.addEventListener('change', validate);

    /* Kalender klik (zet alleen 'Van' als voorbeeld) */
    document.querySelectorAll('[data-day]').forEach(btn=>{
        btn.addEventListener('click', ()=>{
            const day = btn.dataset.day.padStart(2,'0');
            const date = `2022-01-${day}T09:00`;
            from.value = date;
            to.value = `2022-01-${day}T17:00`;
            validate();
        });
    });

    /* Submit/Reset (mock) */
    const toast = document.getElementById('toast');
    document.getElementById('submitBtn').addEventListener('click', ()=>{
        // hier zou je fetch/axios doen; nu alleen een toast
        toast.classList.add('show');
        setTimeout(()=>toast.classList.remove('show'), 2200);
    });

    document.getElementById('resetBtn').addEventListener('click', ()=>{
        from.value = ""; to.value = "";
        document.getElementById('overig').value = "";
        validate();
    });
</script>
</body>
</html>
