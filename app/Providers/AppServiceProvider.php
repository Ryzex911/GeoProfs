<?php

namespace App\Providers;

use App\Models\LeaveRequest;
use App\Observers\LeaveRequestObserver;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ Admin mag alles (bypass policies)
        Gate::before(function ($user, $ability) {
            // Spatie laravel-permission
            if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                return true;
            }

            // Als je géén Spatie gebruikt maar een kolom 'role' hebt, gebruik dit i.p.v. hierboven:
            // if (($user->role ?? null) === 'admin') return true;

            return null; // laat policies/gates beslissen voor niet-admins
        });

        // ✅ Hou bestaande app boot zaken hier (observer/policies/rules)

        // Observer
        LeaveRequest::observe(LeaveRequestObserver::class);

        // Password defaults
        Password::defaults(function () {
            return Password::min(8);
        });

        // Vite prefetch
        Vite::prefetch(concurrency: 3);

        // ✅ Audit auth/security event listeners
        $this->registerAuditEventListeners();
    }

    private function registerAuditEventListeners(): void
    {
        // ✅ SUCCESSFUL LOGIN
        Event::listen(Login::class, function (Login $event) {
            $audit = app(AuditLogger::class);

            $audit->log(
                action: 'auth.login.success',
                auditable: $event->user,
                oldValues: null,
                newValues: [
                    'guard'    => $event->guard,
                    'remember' => $event->remember ?? false,
                ],
                logType: 'security',
                description: 'User logged in'
            );
        });

        // ✅ FAILED LOGIN
        Event::listen(Failed::class, function (Failed $event) {
            $audit = app(AuditLogger::class);

            $audit->log(
                action: 'auth.login.failed',
                auditable: null,
                oldValues: null,
                newValues: [
                    'guard' => $event->guard,
                    'email' => $event->credentials['email'] ?? null,
                ],
                logType: 'security',
                description: 'Failed login attempt'
            );
        });

        // ✅ LOGOUT
        Event::listen(Logout::class, function (Logout $event) {
            $audit = app(AuditLogger::class);

            $audit->log(
                action: 'auth.logout',
                auditable: $event->user,
                oldValues: null,
                newValues: [
                    'guard' => $event->guard,
                ],
                logType: 'security',
                description: 'User logged out'
            );
        });
    }
}
