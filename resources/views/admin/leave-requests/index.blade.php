<h1>Admin – Leave Requests</h1>

@foreach($requests as $req)
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px">

        <p><strong>Employee:</strong> {{ $req->employee?->name ?? 'Onbekend' }}</p>
        <p><strong>Type:</strong> {{ $req->leaveType?->name ?? '-' }}</p>
        <p><strong>Periode:</strong>
            {{ $req->start_date->format('d-m-Y') }}
            →
            {{ $req->end_date->format('d-m-Y') }}
        </p>

        <p><strong>Status:</strong> {{ $req->status }}</p>

        <form method="POST"
              action="{{ route('admin.leave-requests.approve', $req) }}"
              style="display:inline">
            @csrf
            <button>Approve</button>
        </form>

        <form method="POST"
              action="{{ route('admin.leave-requests.reject', $req) }}"
              style="display:inline">
            @csrf
            <button>Reject</button>
        </form>

    </div>
@endforeach
