<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home'); // of je dashboard
});

Route::get('/blabla', function () { return view('auth.login'); })->name('login.form');
Route::get('/reset-password', function () { return view('auth.reset-password'); });

require __DIR__.'/auth.php';
