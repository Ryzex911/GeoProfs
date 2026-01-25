<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LeaveController;
use App\Http\Controllers\Api\LeaveBalanceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function (Request $request) {
    return 'test';
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/leave-balance', [LeaveController::class, 'getBalance'])->name('api.leave-balance');

    // US007: Saldo ophalen voor ingelogde gebruiker
    Route::get('/me/leave-balance', [LeaveBalanceController::class, 'me'])->name('api.me.leave-balance');
});
