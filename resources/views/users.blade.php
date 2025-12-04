@extends('layouts.master')

@section('content')

    <div class="page-container">

        <h2 class="page-title">Gebruikers en rollen</h2>

        <div class="filters-row">
            <input id="searchUsers" type="text" class="search-input" placeholder="Zoek op naam of email">

            <select id="roleFilter" class="filter-select">
                <option value="all">Alle rollen</option>
                @foreach($allRoles as $role)
                    <option value="{{ strtolower($role->name) }}">{{ $role->name }}</option>
                @endforeach
            </select>

            <a href="{{ route('dashboard') }}" class="btn-dashboard">Dashboard</a>
        </div>

        <div class="users-table-card shadow">

            <table class="users-table">
                <thead>
                <tr>
                    <th></th>
                    <th>Naam</th>
                    <th>Email</th>
                    <th>Team</th>
                    <th>Rol(len)</th>
                    <th>Actie</th>
                </tr>
                </thead>

                <tbody>
                @foreach($users as $user)
                    <tr class="user-row"
                        data-name="{{ strtolower($user->name) }}"
                        data-email="{{ strtolower($user->email) }}"
                        data-roles="{{ strtolower($user->roles->pluck('name')->join(',')) }}"
                    >
                        <td><input type="checkbox"></td>

                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>

                        <td>—</td> {{-- Team komt later --}}

                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge role">{{ $role->name }}</span>
                            @endforeach
                        </td>

                        <td class="action-col">
                            <div class="action-dropdown">
                                <button class="action-btn">⋮</button>

                                <ul class="dropdown-menu">
                                    @can('updateRoles', $user)
                                    <li><button class="dropdown-item editRoleBtn" data-user="{{ $user->id }}">Rollen wijzigen</button></li>
                                    @endcan
                                    <li><a class="dropdown-item" href="#">Profiel bewerken</a></li>
                                    <li><a class="dropdown-item text-danger" href="#">Verwijderen</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>

        </div>
    </div>


    {{-- Rollen aanpassen modal --}}
    <div id="roleModal" class="role-modal hidden">
        <div class="modal-box">

            <h3>Rollen aanpassen</h3>

            <form id="roleForm" method="POST">
                @csrf
                @method('PUT')

                <div class="checkbox-group">
                    @foreach($allRoles as $role)
                        <label class="checkbox-item">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}">
                            {{ ucfirst($role->name) }}
                        </label>
                    @endforeach
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-save">Opslaan</button>
                    <button type="button" class="btn-cancel" onclick="closeRoleModal()">Sluiten</button>
                </div>
            </form>

        </div>
    </div>


@endsection

@push('scripts')
    <script src="/js/user-roles.js"></script>
@endpush
