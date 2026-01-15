<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;

class RoleService
{
    private const SESSION_KEY = 'active_role_id';

    public function getAllRoles()
    {
        return Role::all();
    }

    public function setActiveRoleId(int $roleId): void
    {
        session([self::SESSION_KEY => $roleId]);
    }

    public function getActiveRoleId(): ?int
    {
        return session(self::SESSION_KEY);
    }

    public function getActiveRole(User $user): ?Role
    {
        $roleId = $this->getActiveRoleId();

        if (!$roleId) {
            return null;
        }

        return $user->roles()
            ->where('roles.id', $roleId)
            ->first();
    }
}
