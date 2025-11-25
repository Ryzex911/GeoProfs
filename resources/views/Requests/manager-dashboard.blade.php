<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Verlofaanvragen beoordelen — GeoProfs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/manager-requests.css') }}">
</head>
<body>

<!-- Topbar -->
<div class="topbar">
    <div class="topbar-left">
        <div class="logo-dot"></div>
        <div class="topbar-title">GeoProfs</div>
    </div>
    <div class="userbox">
        <span class="user-role-chip">Ingelogd als manager</span>
        <span>Tawfik</span>
        <img src="https://i.pravatar.cc/150?img=23" alt="Profiel">
    </div>
</div>

<main class="container" role="main">
    <!-- Header -->
    <header class="page-header">
        <div>
            <h1 class="page-title">Verlofaanvragen beoordelen</h1>
        </div>
    </header>

    <!-- KPI's -->
    <section class="kpi-row" aria-label="Overzicht verlofaanvragen">
        <div class="kpi-card">
            <div class="kpi-label">Openstaand</div>
            <div class="kpi-value">5 aanvragen</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Vandaag beoordeeld</div>
            <div class="kpi-value">2 aanvragen</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Deze maand totaal</div>
            <div class="kpi-value">18 aanvragen</div>
        </div>
    </section>

    <!-- Hoofd card -->
    <section class="card" aria-label="Lijst met verlofaanvragen">
        <!-- Filters -->
        <div class="filter-row">
            <div class="field">
                <label for="search">Zoeken</label>
                <input id="search" type="text" placeholder="Zoek op naam of e-mailadres">
            </div>
            <div class="field field-small">
                <label for="statusFilter">Status</label>
                <select id="statusFilter">
                    <option>Alle statussen</option>
                    <option>In afwachting</option>
                    <option>Goedgekeurd</option>
                    <option>Afgekeurd</option>
                </select>
            </div>
            <div class="field field-small">
                <label for="reasonFilter">Reden</label>
                <select id="reasonFilter">
                    <option>Alle redenen</option>
                    <option>TVR</option>
                    <option>Verlof</option>
                    <option>Overig</option>
                </select>
            </div>
            <div class="field field-small">
                <label for="dateFilter">Datum</label>
                <select id="dateFilter">
                    <option>Alle data</option>
                    <option>Deze week</option>
                    <option>Deze maand</option>
                    <option>Komende 3 maanden</option>
                </select>
            </div>
        </div>

        <!-- Tabel -->
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Medewerker</th>
                    <th>Reden</th>
                    <th>Periode</th>
                    <th>Uren</th>
                    <th>Status</th>
                    <th>Acties</th>
                </tr>
                </thead>
                <tbody>
                <!-- Voorbeeld rijen (later vervangen door data uit database) -->
                <tr data-request-id="1" data-employee="Sophie Janssen">
                    <td>
                        <div class="name-cell">
                            <div class="name-avatar"></div>
                            <div class="name-text">
                                <span class="name-main">Sophie Janssen</span>
                                <span class="name-sub">Team Noord</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="reason-pill">Verlof</span></td>
                    <td>12 jan 2026 09:00 — 12 jan 2026 17:00</td>
                    <td>8 uur</td>
                    <td><span class="status-pill status-pending">In afwachting</span></td>
                    <td class="actions-cell">
                        <button class="btn-chip btn-approve" type="button">
                            Goedkeuren
                        </button>
                        <button class="btn-chip btn-decline" type="button">
                            Afkeuren
                        </button>
                    </td>
                </tr>

                <tr data-request-id="2" data-employee="Lars de Vries">
                    <td>
                        <div class="name-cell">
                            <div class="name-avatar"></div>
                            <div class="name-text">
                                <span class="name-main">Lars de Vries</span>
                                <span class="name-sub">Team Zuid</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="reason-pill">Ziekte</span></td>
                    <td>18 jan 2026 08:00 — 19 jan 2026 17:00</td>
                    <td>16 uur</td>
                    <td><span class="status-pill status-pending">In afwachting</span></td>
                    <td class="actions-cell">
                        <button class="btn-chip btn-approve" type="button">
                            Goedkeuren
                        </button>
                        <button class="btn-chip btn-decline" type="button">
                            Afkeuren
                        </button>
                    </td>
                </tr>

                <tr data-request-id="3" data-employee="Nina van Dijk">
                    <td>
                        <div class="name-cell">
                            <div class="name-avatar"></div>
                            <div class="name-text">
                                <span class="name-main">Nina van Dijk</span>
                                <span class="name-sub">HR / Beheer</span>
                            </div>
                        </div>
                    </td>
                    <td><span class="reason-pill">Overig</span></td>
                    <td>25 jan 2026 09:00 — 26 jan 2026 17:00</td>
                    <td>16 uur</td>
                    <td><span class="status-pill status-pending">In afwachting</span></td>
                    <td class="actions-cell">
                        <button class="btn-chip btn-approve" type="button">
                            Goedkeuren
                        </button>
                        <button class="btn-chip btn-decline" type="button">
                            Afkeuren
                        </button>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
    </section>
</main>

<!-- Modal voor afkeuren -->
<div class="modal-backdrop" id="declineModal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="declineTitle">
        <div class="modal-header">
            <h2 class="modal-title" id="declineTitle">Aanvraag afkeuren</h2>
            <button class="modal-close" type="button" id="declineClose">&times;</button>
        </div>
        <div class="modal-body">
            <p id="declineIntro">
                Je staat op het punt de aanvraag van <strong id="declineEmployee">medewerker</strong> af te keuren.
            </p>
            <p>Je kunt hieronder een reden invullen zodat de medewerker weet waarom de aanvraag is afgekeurd (niet verplicht).</p>
            <textarea id="declineReason" placeholder="Bijvoorbeeld: planning is al vol in deze periode..."></textarea>
        </div>
        <div class="modal-footer">
            <button class="btn-small btn-secondary" type="button" id="declineCancel">Annuleren</button>
            <button class="btn-small btn-danger" type="button" id="declineSubmit">
                Afkeuren (optionele reden)
            </button>
        </div>
    </div>
</div>

<script>
    // Simpele frontend-logica voor goedkeuren / afkeuren
    const table = document.querySelector('table tbody');
    const declineModal = document.getElementById('declineModal');
    const declineEmployee = document.getElementById('declineEmployee');
    const declineReason = document.getElementById('declineReason');
    const declineSubmit = document.getElementById('declineSubmit');
    const declineClose = document.getElementById('declineClose');
    const declineCancel = document.getElementById('declineCancel');

    let activeRow = null;
    let activeRequestId = null;

    function closeModal() {
        declineModal.classList.remove('is-visible');
        declineReason.value = '';
        activeRow = null;
        activeRequestId = null;
    }

    // Klik op Goedkeuren of Afkeuren in de tabel
    table.addEventListener('click', function (e) {
        const approveBtn = e.target.closest('.btn-approve');
        const declineBtn = e.target.closest('.btn-decline');

        if (!approveBtn && !declineBtn) return;

        const row = e.target.closest('tr');
        const statusCell = row.querySelector('.status-pill');
        const employeeName = row.dataset.employee;
        const requestId = row.dataset.requestId;

        if (approveBtn) {
            // Later vervangen door call naar Laravel (controller)
            statusCell.textContent = 'Goedgekeurd';
            statusCell.className = 'status-pill status-approved';
            return;
        }

        if (declineBtn) {
            activeRow = row;
            activeRequestId = requestId;
            declineEmployee.textContent = employeeName;
            declineModal.classList.add('is-visible');
        }
    });

    // Modal sluiten (kruisje of Annuleren)
    declineClose.addEventListener('click', closeModal);
    declineCancel.addEventListener('click', closeModal);

    // Afkeuren-knop in de modal
    declineSubmit.addEventListener('click', function () {
        const reason = declineReason.value.trim(); // reden is optioneel

        if (!activeRow) return;

        const statusCell = activeRow.querySelector('.status-pill');
        statusCell.textContent = 'Afgekeurd';
        statusCell.className = 'status-pill status-declined';

        // Hier later: POST naar Laravel route, bijvoorbeeld:
        // fetch('/manager/requests/' + activeRequestId + '/decline', {
        //     method: 'POST',
        //     headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
        //     body: JSON.stringify({ reason })
        // });

        console.log('Aanvraag afgekeurd', activeRequestId, 'reden (optioneel):', reason);
        closeModal();
    });

    // Klik buiten de modal = sluiten
    declineModal.addEventListener('click', function (e) {
        if (e.target === declineModal) {
            closeModal();
        }
    });
</script>

</body>
</html>
