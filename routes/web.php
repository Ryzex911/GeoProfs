<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Services\RoleService;
use Illuminate\Support\Facades\Route;

// Redirect root
Route::redirect('/', '/login');

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.perform');

Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware(['guest','throttle:6,1'])->name('password.email');

Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')->name('password.store');

// 2FA
Route::middleware('2fa.pending')->group(function () {
    Route::get('/2fa',  [TwoFactorController::class, 'show'])->name('2fa.show');
    Route::post('/2fa', [TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('/2fa/resend', [TwoFactorController::class, 'resend'])->name('2fa.resend');
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')->name('logout');

// Werknemer dashboard
Route::get('/dashboard', fn () => view('Requests.dashboard'))
    ->middleware('auth')
    ->name('dashboard');

// Users & Roles (for admin only)
Route::middleware('auth')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/roles', [UserController::class, 'updateUserRoles'])->name('users.updateRoles');
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

// Admin dashboard
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
    ->middleware('auth')
    ->name('admin.dashboard');

Route::post('/switch-role', [RoleController::class, 'switch'])
    ->name('role.switch')
    ->middleware('auth');


Route::get('/debug-role', function (RoleService $roleService) {
    $user = auth()->user();

    if (!$user) {
        return 'Niet ingelogd';
    }

    $activeRoleId = $roleService->getActiveRoleId();
    $activeRole = $roleService->getActiveRole($user);

    dd([
        'active_role_id_in_session' => $activeRoleId,
        'active_role' => $activeRole?->only(['id', 'name']),
        'user_roles' => $user->roles()->get(['id', 'name'])->toArray(),
        'php_version' => phpversion(),
        'php_version_id' => PHP_VERSION_ID,
    ]);
})->middleware('auth');
