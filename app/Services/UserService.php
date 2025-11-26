<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{

    // Alle gebruikers met hun rollen ophalen
    public function getAllUsers(): Collection
    {
        // Eager loading voorkomt n+1 probleem
        return User::with('roles')->get();
    }

    // Alle rollen ophalen
    public function getAllRoles(): Collection
    {
        return Role::all();
    }

    // Gebruikersrollen bijwerken via privot tabel
    public function updateRoles(User $user, array $roleIds): void
    {
        $user->roles()->sync($roleIds);
    }
}
