<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use App\Models\LeaveRequest;
use App\Observers\LeaveRequestObserver;
use Illuminate\Support\Facades\Gate;


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
        Vite::prefetch(concurrency: 3);

        Password::defaults(function () {
            return Password::min(8); // minimaal 6 tekens
        });

        LeaveRequest::observe(LeaveRequestObserver::class);


        Gate::policy(User::class, UserPolicy::class);
    }
}
