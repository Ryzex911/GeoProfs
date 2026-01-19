<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveRole
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        if (auth()->check() && !session()->has('active_role_id')) {
            $firstRoleId = auth()->user()->roles()->pluck('roles.id')->first();

            if ($firstRoleId) {
                session(['active_role_id' => (int)$firstRoleId]);
            }
        }

        return $next($request);

    }
}
