<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;



// Always go to login when opening the site
Route::redirect('/', '/login');

// Login + Reset views with proper route names
// Toon login-formulier
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// Verwerk login
Route::post('/login', [LoginController::class, 'login'])->name('login.perform');

// 1) Forgot password (request reset link)
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware(['guest','throttle:6,1'])
    ->name('password.email');

// 2) Reset password (after clicking email link)
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');


Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');


// Route om code te tonen + mail te versturen
Route::get('/2fa', [TwoFactorController::class, 'show'])->name('2fa.show');

// Route om code te controleren (na invullen)
Route::post('/2fa', [TwoFactorController::class, 'verify'])->name('2fa.verify');


Route::get('/dashboard', function () {
    return view('dashboard'); // verwijst naar resources/views/dashboard.blade.php
})->middleware('auth')->name('dashboard');
