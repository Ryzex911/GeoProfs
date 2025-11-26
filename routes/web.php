<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::redirect('/', '/login');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.perform');

// Forgot password (send reset link)
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware(['guest','throttle:6,1'])->name('password.email');

// Reset password (open form with token, then save)
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')->name('password.store');

// 2FA is alleen bereikbaar als er een pending-2FA sessie is
Route::middleware('2fa.pending')->group(function () {
    Route::get('/2fa',  [TwoFactorController::class, 'show'])->name('2fa.show');
    Route::post('/2fa', [TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('/2fa/resend', [TwoFactorController::class, 'resend'])->name('2fa.resend');
});

Route::get('/dashboard', fn () => view('Requests.dashboard'))->middleware('auth')->name('dashboard');

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');


Route::post('/2fa/resend', [TwoFactorController::class, 'resend'])
    ->middleware('2fa.pending')
    ->name('2fa.resend');

// User & Role systeem routes (alleen voor ingelogde users)
Route::middleware('auth')->group(function () {

    Route::get('/users', [UserController::class, 'index'])
        ->name('users.index');

    Route::put('/users/{user}/roles', [UserController::class, 'updateUserRoles'])
        ->name('users.updateRoles');

    Route::get('/roles', [RoleController::class, 'index'])
        ->name('roles.index');
});
