<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Bepaal of de gebruiker de gebruikerslijst mag zien
     * Alleen admins hebben toegang
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Bepaal of de gebruiker rollen mag aanpassen
     * Alleen admins hebben toegang
     */
    public function updateRoles(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
