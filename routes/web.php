<?php

use Illuminate\Support\Facades\Route;

// Always go to login when opening the site
Route::redirect('/', '/login');

// Login + Reset views with proper route names
Route::view('/login', 'auth.login')->name('login');
Route::view('/reset-password', 'auth.reset-password')->name('password.request');
