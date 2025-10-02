<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;

//Route::middleware('auth')->group(function () {
 //   Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
 //   Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
//});
Route::post('/login', [LoginController::class, 'login'])->name('login');
// SPA routes
Route::view('/', 'app')->name('spa');
Route::view('/login', 'app');
Route::view('/reset-password', 'app');

// Auth routes



require __DIR__.'/auth.php';
