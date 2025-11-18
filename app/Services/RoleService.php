<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;

class RoleService
{

    // Alle rollen ophalen
    public function getAllRoles()
    {
        return Role::all();
    }
}
