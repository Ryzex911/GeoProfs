@php
    use App\Models\LeaveRequest;

    $isDeletedView = $isDeletedView ?? false;
@endphp

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
            <h1 class="page-title">{{ $isDeletedView ? 'Verwijderde aanvragen' : 'Verlofaanvragen beoordelen' }}</h1>

            <div style="margin-top: 12px; display: flex; gap: 10px; flex-wrap: wrap;">
                @if(!$isDeletedView)
                    <a class="btn-chip" href="{{ route('manager.requests.deleted') }}">
                        Verwijderde aanvragen bekijken
                    </a>
                @else
                    <a class="btn-chip" href="{{ route('manager.requests.index') }}">
                        Terug naar aanvragen
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- KPI's -->
    <section class="kpi-row" aria-label="Overzicht verlofaanvragen">
        <div class="kpi-card">
            <div class="kpi-label">Openstaand</div>
            <div class="kpi-value">{{ $kpiOpen ?? 0 }} aanvragen</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Vandaag beoordeeld</div>
            <div class="kpi-value">{{ $kpiReviewedToday ?? 0 }} aanvragen</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Deze maand totaal</div>
            <div class="kpi-value">{{ $kpiMonthTotal ?? 0 }} aanvragen</div>
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
                    <option value="">Alle statussen</option>
                    <option value="pending">In afwachting</option>
                    <option value="approved">Goedgekeurd</option>
                    <option value="rejected">Afgekeurd</option>
                    <option value="canceled">Geannuleerd</option>
                </select>
            </div>

            <div class="field field-small">
                <label for="reasonFilter">Reden</label>
                <select id="reasonFilter">
                    <option value="">Alle redenen</option>
                    <option value="tvt">TVT</option>
                    <option value="vakantie">Vakantie</option>
                    <option value="anders">Anders</option>
                    <option value="verlof">Verlof</option>
                    <option value="overig">Overig</option>
                </select>
            </div>

            <div class="field field-small">
                <label for="dateFilter">Datum</label>
                <select id="dateFilter">
                    <option value="">Alle data</option>
                    <option value="week">Deze week</option>
                    <option value="month">Deze maand</option>
                    <option value="3months">Komende 3 maanden</option>
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
                @forelse ($requests as $request)
                    @php
                        $employeeName = $request->employee?->name ?? 'Onbekend';
                        $employeeSub  = $request->employee?->email ?? '';

                        $reasonLabel = $request->leaveType?->name ?? ($request->reason ?? '—');

                        $statusLabel = match($request->status) {
                            LeaveRequest::STATUS_PENDING  => 'In afwachting',
                            LeaveRequest::STATUS_APPROVED => 'Goedgekeurd',
                            LeaveRequest::STATUS_REJECTED => 'Afgekeurd',
                            LeaveRequest::STATUS_CANCELED => 'Geannuleerd',
                            default => $request->status,
                        };

                        $statusClass = match($request->status) {
                            LeaveRequest::STATUS_PENDING  => 'status-pending',
                            LeaveRequest::STATUS_APPROVED => 'status-approved',
                            LeaveRequest::STATUS_REJECTED => 'status-declined',
                            LeaveRequest::STATUS_CANCELED => 'status-canceled',
                            default => 'status-pending',
                        };

                        $start = $request->start_date?->format('d M Y H:i') ?? '-';
                        $end   = $request->end_date?->format('d M Y H:i') ?? '-';

                        $hours = ($request->start_date && $request->end_date)
                            ? round($request->start_date->diffInMinutes($request->end_date) / 60, 2)
                            : 0;

                        $isPending = ($request->status === LeaveRequest::STATUS_PENDING);
                    @endphp

                    <tr
                        data-request-id="{{ $request->id }}"
                        data-employee="{{ strtolower($employeeName) }}"
                        data-email="{{ strtolower($employeeSub) }}"
                        data-status="{{ $request->status }}"
                        data-reason="{{ strtolower($reasonLabel) }}"
                        data-start="{{ $request->start_date?->format('Y-m-d') }}"
                    >
                        <td>
                            <div class="name-cell">
                                <div class="name-avatar"></div>
                                <div class="name-text">
                                    <span class="name-main">{{ $employeeName }}</span>
                                    <span class="name-sub">{{ $employeeSub }}</span>
                                </div>
                            </div>
                        </td>

                        <td><span class="reason-pill">{{ $reasonLabel }}</span></td>
                        <td>{{ $start }} — {{ $end }}</td>
                        <td>{{ rtrim(rtrim(number_format($hours, 2), '0'), '.') }} uur</td>

                        <td>
                            <span class="status-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>

                        <td class="actions-cell">
                            @if($isDeletedView)
                                {{-- Deleted view: alleen herstellen --}}
                                <form method="POST" action="{{ route('manager.requests.restore', $request->id) }}" style="display:inline;">
                                    @csrf
{{--                                    <button class="btn-chip" type="submit">Herstellen</button>--}}
                                </form>
                            @else
                                {{-- Normale view --}}
                                @if($isPending)
                                    <form method="POST" action="{{ route('admin.leave-requests.approve', $request) }}" style="display:inline;">
                                        @csrf
                                        <button class="btn-chip btn-approve" type="submit">Goedkeuren</button>
                                    </form>

                                    <button class="btn-chip btn-decline" type="button">Afkeuren</button>

                                    <form id="reject-form-{{ $request->id }}" method="POST" action="{{ route('admin.leave-requests.reject', $request) }}" style="display:none;">
                                        @csrf
                                    </form>
                                @endif

                                {{-- Verbergen mag altijd in normale view (ook approved/rejected),
                                     als je dit alleen voor pending wilt: zet deze ook binnen if($isPending) --}}
                                <form method="POST" action="{{ route('manager.requests.hide', $request) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-chip" type="submit">Verwijder</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">{{ $isDeletedView ? 'Geen verwijderde aanvragen.' : 'Geen aanvragen gevonden.' }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</main>

<!-- Modal voor afkeuren (alleen nodig in normale view) -->
@if(!$isDeletedView)
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
@endif

<script>
    // Modal logic (alleen relevant als er afkeur-knoppen zijn)
    (function () {
        const table = document.querySelector('table tbody');
        const declineModal = document.getElementById('declineModal');
        if (!table || !declineModal) return;

        const declineEmployee = document.getElementById('declineEmployee');
        const declineReason = document.getElementById('declineReason');
        const declineSubmit = document.getElementById('declineSubmit');
        const declineClose = document.getElementById('declineClose');
        const declineCancel = document.getElementById('declineCancel');

        let activeRequestId = null;

        function closeModal() {
            declineModal.classList.remove('is-visible');
            if (declineReason) declineReason.value = '';
            activeRequestId = null;
        }

        table.addEventListener('click', function (e) {
            const declineBtn = e.target.closest('.btn-decline');
            if (!declineBtn) return;

            const row = declineBtn.closest('tr');
            activeRequestId = row?.dataset?.requestId || null;

            if (declineEmployee) declineEmployee.textContent = row?.dataset?.employee || 'medewerker';
            declineModal.classList.add('is-visible');
        });

        declineClose?.addEventListener('click', closeModal);
        declineCancel?.addEventListener('click', closeModal);

        declineModal.addEventListener('click', function (e) {
            if (e.target === declineModal) closeModal();
        });

        declineSubmit?.addEventListener('click', function () {
            if (!activeRequestId) return;
            const form = document.getElementById('reject-form-' + activeRequestId);
            if (form) form.submit();
        });
    })();
</script>

<script>
    // Client-side filters (met persist)
    (function () {
        const searchInput  = document.getElementById('search');
        const statusFilter = document.getElementById('statusFilter');
        const reasonFilter = document.getElementById('reasonFilter');
        const dateFilter   = document.getElementById('dateFilter');

        if (!searchInput || !statusFilter || !reasonFilter || !dateFilter) return;

        function getRows() {
            return Array.from(document.querySelectorAll('table tbody tr[data-request-id]'));
        }

        function inRange(dateStr, mode) {
            if (!mode || !dateStr) return true;

            const d = new Date(dateStr + 'T00:00:00');
            const now = new Date();

            if (mode === 'week') {
                const day = (now.getDay() + 6) % 7; // maandag=0
                const start = new Date(now);
                start.setDate(now.getDate() - day);
                start.setHours(0,0,0,0);

                const end = new Date(start);
                end.setDate(start.getDate() + 7);

                return d >= start && d < end;
            }

            if (mode === 'month') {
                return d.getFullYear() === now.getFullYear() && d.getMonth() === now.getMonth();
            }

            if (mode === '3months') {
                const end = new Date(now);
                end.setMonth(end.getMonth() + 3);
                return d >= now && d <= end;
            }

            return true;
        }

        function applyFilters() {
            const q  = (searchInput.value || '').trim().toLowerCase();
            const st = statusFilter.value;
            const rs = reasonFilter.value;
            const dm = dateFilter.value;

            localStorage.setItem('mgr_q', q);
            localStorage.setItem('mgr_st', st);
            localStorage.setItem('mgr_rs', rs);
            localStorage.setItem('mgr_dm', dm);

            getRows().forEach(row => {
                const emp = row.dataset.employee || '';
                const email = row.dataset.email || '';
                const rowStatus = row.dataset.status || '';
                const rowReason = (row.dataset.reason || '').toLowerCase();
                const rowStart  = row.dataset.start || '';

                const matchSearch = !q || emp.includes(q) || email.includes(q);
                const matchStatus = !st || rowStatus === st;
                const matchReason = !rs || rowReason.includes(rs);
                const matchDate   = inRange(rowStart, dm);

                row.style.display = (matchSearch && matchStatus && matchReason && matchDate) ? '' : 'none';
            });
        }

        // restore saved filters
        searchInput.value  = localStorage.getItem('mgr_q') || '';
        statusFilter.value = localStorage.getItem('mgr_st') || '';
        reasonFilter.value = localStorage.getItem('mgr_rs') || '';
        dateFilter.value   = localStorage.getItem('mgr_dm') || '';

        applyFilters();

        searchInput.addEventListener('input', applyFilters);
        statusFilter.addEventListener('change', applyFilters);
        reasonFilter.addEventListener('change', applyFilters);
        dateFilter.addEventListener('change', applyFilters);
    })();
</script>

</body>
</html>
