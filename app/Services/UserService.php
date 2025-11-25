<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;

class UserService
{

    public function getAllUsers()
    {
        return User::with('roles')->get();
    }

    public function updateRoles(User $user, array $roleIds)
    {
        $user->roles()->sync($roleIds);
    }
}
