<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTwoFactorIsPending
{
    public function handle(Request $request, Closure $next)
    {
        // Alleen doorlaten als er een pending 2FA-sessie is
        if (! $request->session()->has('2fa:user:id')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Log eerst in om 2FA te voltooien.',
            ]);
        }

        return $next($request);
    }
}
