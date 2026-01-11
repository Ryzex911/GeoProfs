@php
    use App\Models\LeaveRequest;
@endphp

    <!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Leave Requests – GeoProfs</title>

    {{-- Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('css/request-dashboard.css') }}">
</head>

<body>

{{-- ========================= --}}
{{-- NAVBAR (hardcoded)       --}}
{{-- ========================= --}}
<div class="topbar">

    {{-- Terug naar dashboard --}}
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">
        ← Dashboard
    </a>

    {{-- Profiel (klikbaar voor later) --}}
    <div class="userbox">
        <span>{{ auth()->user()->name ?? 'Gebruiker' }}</span>
        <a href="#">
            <img src="https://i.pravatar.cc/100" alt="Profiel">
        </a>
    </div>
</div>

{{-- ========================= --}}
{{-- PAGINA INHOUD            --}}
{{-- ========================= --}}
<div class="container">

    <div class="card">
        <div class="card__header">
            <h1 class="card__title">Leave Requests</h1>
        </div>

        <div class="card__body">

            <table class="table">
                <thead>
                <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Reden</th>
                    <th>Acties</th>
                </tr>
                </thead>

                <tbody>
                @foreach ($Requests as $req)
                    <tr>

                        <td>{{ $req->employee->name ?? 'Onbekend' }}</td>

                        <td>{{ $req->leaveType->name ?? 'Onbekend' }}</td>

                        <td>
                            {{ $req->start_date->format('d-m-Y') }}
                            →
                            {{ $req->end_date->format('d-m-Y') }}
                        </td>

                        <td>
                            <span class="status status-{{ $req->status }}">
                                {{ ucfirst($req->status) }}
                            </span>
                        </td>

                        <td class="reason">
                            {{ $req->reason ?: 'Geen reden' }}
                        </td>

                        <td class="actions">

                            {{-- ALS STATUS = INGEDIEND --}}


                            @if ($req->status === LeaveRequest::STATUS_PENDING)

                                <form action="{{ route('leave-requests.cancel', $req->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                            class="btn btn-danger"
                                            onclick="return confirm('Weet je zeker dat je deze aanvraag wilt annuleren?')">
                                        Annuleren
                                    </button>
                                </form>

                            @elseif ($req->status === LeaveRequest::STATUS_CANCELED)

                                <form action="{{ route('leave-requests.destroy', $req->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-secondary"
                                            onclick="return confirm('Weet je zeker dat je deze aanvraag wilt verwijderen?')">
                                        Verwijderen
                                    </button>
                                </form>

                            @else
                                <span class="small-note">Geen acties</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>

        </div>
    </div>

</div>

</body>
</html>
