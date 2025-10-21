<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\LoginController;

// Always go to login when opening the site
Route::redirect('/', '/login');

// Login + Reset views with proper route names
// Toon login-formulier
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// Verwerk login
Route::post('/login', [LoginController::class, 'login'])->name('login.perform');

Route::view('/reset-password', 'auth.reset-password')->name('password.request');


Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');


// Route om code te tonen + mail te versturen
Route::get('/2fa', [TwoFactorController::class, 'show'])->name('2fa.show');

// Route om code te controleren (na invullen)
Route::post('/2fa', [TwoFactorController::class, 'verify'])->name('2fa.verify');


Route::get('/dashboard', function () {
    return view('dashboard'); // verwijst naar resources/views/dashboard.blade.php
})->middleware('auth')->name('dashboard');
