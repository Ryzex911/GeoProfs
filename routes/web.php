<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\LeaveApprovalController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Services\RoleService;
use Illuminate\Support\Facades\Route;

/**
 * Root → login
 */
Route::redirect('/', '/login');

/**
 * AUTH: login + logout
 */
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.perform');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/**
 * AUTH: password reset flow
 */
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware(['guest','throttle:6,1'])
    ->name('password.email');

Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');

/**
 * AUTH: 2FA (alleen als er pending 2FA sessie is)
 */
Route::middleware('2fa.pending')->group(function () {
    Route::get('/2fa',  [TwoFactorController::class, 'show'])->name('2fa.show');
    Route::post('/2fa', [TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('/2fa/resend', [TwoFactorController::class, 'resend'])->name('2fa.resend');
});

/**
 * EMPLOYEE: dashboard + leave request form
 */
Route::middleware('auth')->group(function () {
    // medewerker dashboard overzicht
    Route::get('/dashboard', [LeaveController::class, 'dashboardOverview'])->name('dashboard');

    // pagina om verlof aan te vragen (form)
    Route::get('/requestdashboard', [LeaveController::class, 'dashboard'])->name('requestdashboard');

    // eigen leave requests overzicht + CRUD
    Route::get('/leave-requests', [LeaveController::class, 'index'])->name('leave-requests.index');
    Route::post('/leave-requests', [LeaveController::class, 'store'])->name('leave-requests.store');
    Route::patch('/leave-requests/{leaveRequest}/cancel', [LeaveController::class, 'cancel'])->name('leave-requests.cancel');
    Route::delete('/leave-requests/{leaveRequest}', [LeaveController::class, 'destroy'])->name('leave-requests.destroy');

    // role switch tijdens sessie (active_role_id)
    Route::post('/switch-role', [RoleController::class, 'switch'])->name('role.switch');

    // debug role info (handig tijdens ontwikkeling)
    Route::get('/debug-role', function (RoleService $roleService) {
        $user = auth()->user();
        if (!$user) return 'Niet ingelogd';

        dd([
            'session_role_id' => session('active_role_id'),
            'user_roles' => $user->roles->pluck('id', 'name'),
        ]);
    });
});

/**
 * ADMIN: user + role management (alleen admin)
 */
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/roles', [UserController::class, 'updateUserRoles'])->name('users.updateRoles');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');

    // audit logs bekijken (admin-only)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/audit-logs/export', [AuditLogController::class, 'export'])->name('audit.export');
    });

});

/**
 * MANAGER/PROJECTLEIDER/ADMIN: requests board (approve/reject + deleted view)
 * (toegang voor deze rollen, ongeacht active role)
 */
Route::prefix('manager')
    ->middleware(['auth', 'role:manager,projectleider,admin'])
    ->group(function () {
        Route::get('/requests', [LeaveApprovalController::class, 'index'])->name('manager.requests.index');
        Route::get('/requests/deleted', [LeaveApprovalController::class, 'deleted'])->name('manager.requests.deleted');

        Route::post('/requests/{leaveRequest}/approve', [LeaveApprovalController::class, 'approve'])->name('manager.requests.approve');
        Route::post('/requests/{leaveRequest}/reject', [LeaveApprovalController::class, 'reject'])->name('manager.requests.reject');

        Route::delete('/requests/{leaveRequest}', [LeaveApprovalController::class, 'hide'])->name('manager.requests.hide');
        Route::post('/requests/{id}/restore', [LeaveApprovalController::class, 'restore'])->name('manager.requests.restore');

        // manager dashboard view (als je die gebruikt)
        Route::get('/dashboard', function () {
            return view('Manager.Manager-dashboard');
        })->name('manager.dashboard');
    });

/**
 * ADMIN (optioneel): admin leave requests routes
 * (je gebruikt dezelfde controller als manager)
 */
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/leave-requests', [LeaveApprovalController::class, 'index'])->name('admin.leave-requests.index');
        Route::post('/leave-requests/{leaveRequest}/approve', [LeaveApprovalController::class, 'approve'])->name('admin.leave-requests.approve');
        Route::post('/leave-requests/{leaveRequest}/reject', [LeaveApprovalController::class, 'reject'])->name('admin.leave-requests.reject');
    });
Route::get('/manager/requests/{leaveRequest}/proof', [LeaveApprovalController::class, 'proof'])
    ->middleware('auth')
    ->name('manager.requests.approve');

Route::post('/manager/requests/{leaveRequest}/reject', [LeaveApprovalController::class, 'reject'])
    ->middleware('auth')
    ->name('manager.requests.reject');


Route::get('/manager/dashboard', function () {
    return view('Manager.Manager-dashboard');
})->middleware(['auth']);

Route::post('/switch-role', [RoleController::class, 'switch'])
    ->name('role.switch')
    ->middleware('auth');


Route::get('/debug-role', function (RoleService $roleService) {
    $user = auth()->user();

    if (!$user) {
        return 'Niet ingelogd';
    }



})->middleware('auth');
