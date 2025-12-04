@extends('layouts.admin')

@section('admin-content')
    <div class="dashboard-container">

        <div class="card-btn" onclick="window.location='/admin/verlof'">
            Verlof aanvragen
        </div>

        <div class="card-btn" onclick="window.location='/users'">
            Mijn medewerkers
        </div>

    </div>

@endsection
