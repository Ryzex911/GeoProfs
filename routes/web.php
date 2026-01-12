<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\LeaveController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\LeaveApprovalController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// Redirect root
Route::redirect('/', '/login');

// Authentication
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


//Hier is de route naar de medewerker dashboard na het inloggen om zijn overzicht te zien
Route::get('/dashboard', [LeaveController::class, 'dashboardOverview'])
    ->middleware('auth')
    ->name('dashboard');


//Dit is de route naar de verlof aanvraag pagina met form en reden etc..
Route::get('/requestdashboard', [LeaveController::class, 'dashboard'])
    ->middleware('auth')
    ->name('requestdashboard');

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

//dat is de 2fa opnieuw stuur knopje methode
Route::post('/2fa/resend', [TwoFactorController::class, 'resend'])
    ->middleware('2fa.pending')
    ->name('2fa.resend');

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

//dit is voor het laten zien van de leave requests
Route::middleware(['auth'])->group(function () {

    Route::get('/leave-requests', [LeaveController::class, 'index'])
        ->name('leave-requests.index');

    Route::post('/leave-requests', [LeaveController::class, 'store'])
        ->name('leave-requests.store');

    Route::patch('/leave-requests/{leaveRequest}/cancel',
        [LeaveController::class, 'cancel'])
        ->name('leave-requests.cancel');

    Route::delete('/leave-requests/{leaveRequest}',
        [LeaveController::class, 'destroy'])
        ->name('leave-requests.destroy');

});


Route::get('/admin/leave-requests', [LeaveApprovalController::class, 'index'])
    ->name('admin.leave-requests.index');

Route::post('/admin/leave-requests/{leaveRequest}/approve', [LeaveApprovalController::class, 'approve'])
    ->name('admin.leave-requests.approve');

Route::post('/admin/leave-requests/{leaveRequest}/reject', [LeaveApprovalController::class, 'reject'])
    ->name('admin.leave-requests.reject');



Route::get('/manager/requests', [LeaveApprovalController::class, 'index'])
    ->middleware('auth')
    ->name('manager.requests.index');

Route::delete('/manager/requests/{leaveRequest}', [LeaveApprovalController::class, 'hide'])
    ->middleware('auth')
    ->name('manager.requests.hide');

Route::get('/manager/requests/deleted', [LeaveApprovalController::class, 'deleted'])
    ->middleware('auth')
    ->name('manager.requests.deleted');

Route::post('/manager/requests/{id}/restore', [LeaveApprovalController::class, 'restore'])
    ->middleware('auth')
    ->name('manager.requests.restore');


Route::get('/manager/requests', function () {
    return view('Requests.manager-requestsboard');
})->middleware(['auth']);

Route::get('/manager/dashboard', function () {
    return view('Manager.Manager-dashboard');
})->middleware(['auth']);
