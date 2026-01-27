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
        // ✅ Hou bestaande app boot zaken hier (observer/policies/rules)
        // Observer (als jullie die gebruiken)
        LeaveRequest::observe(LeaveRequestObserver::class);

        // (optioneel) Password defaults als jullie dat hadden
        Password::defaults(function () {
            return Password::min(8);
        });

        // (optioneel) Vite prefetch als jullie dat hadden
        Vite::prefetch(concurrency: 3);

        // (optioneel) Gates/policies (laat staan als je ze gebruikt)
        // Voorbeeld:
        // Gate::define('viewAuditLogs', fn ($user) => $user->roles()->where('name', 'admin')->exists());

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
