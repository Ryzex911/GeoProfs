<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        // Als je session-based active role gebruikt:
        // (optioneel, maar handig bij switch-role)
        $activeRoleId = session('active_role_id');

        // Support: role names zoals 'admin' of eventueel role ids
        foreach ($roles as $role) {

            // 1) Check op actieve role id
            if ($activeRoleId && ctype_digit((string) $role) && (int) $activeRoleId === (int) $role) {
                return $next($request);
            }

            // 2) Check op roles relatie (many-to-many)
            if (method_exists($user, 'roles')) {
                // naam check
                if (!ctype_digit((string) $role)) {
                    if ($user->roles()->where('name', $role)->exists()) {
                        return $next($request);
                    }
                } else {
                    // id check
                    if ($user->roles()->where('roles.id', (int) $role)->exists()) {
                        return $next($request);
                    }
                }
            }
        }

        abort(403, 'Forbidden');
    }
}
