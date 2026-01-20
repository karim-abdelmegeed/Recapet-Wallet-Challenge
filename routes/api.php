<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\WalletController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\API\SnapshotBalanceController;


Route::prefix('auth')->middleware('throttle:auth_actions')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum'])->prefix('wallet')->group(function () {
    Route::post('/deposit', [WalletController::class, 'deposit']);
    Route::post('/withdraw', [WalletController::class, 'withdraw']);
    Route::post('/transfer', [WalletController::class, 'transfer']);
    Route::get('/snapshot-balance', [SnapshotBalanceController::class, 'index']);
});
