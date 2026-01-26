<?php

use App\Http\Middleware\EnsureActiveRole;
use App\Http\Middleware\EnsureTwoFactorIsPending;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RequestIdMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ✅ Route middleware aliases (gebruik je in routes/web.php)
        $middleware->alias([
            '2fa.pending' => EnsureTwoFactorIsPending::class,
            'role'        => RoleMiddleware::class, // maakt 'role:admin' mogelijk
        ]);

        // ✅ Optionele cookie-excepties
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // ✅ Web middleware chain (in volgorde)
        $middleware->web(append: [
            RequestIdMiddleware::class,            // ⬅️ stap 4: request_id voor audit correlatie
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            EnsureActiveRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
