<?php

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('profile.edit');
    }
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('profile', function () {
    return Inertia::render('Profile');
})->name('profile.edit')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::get('wallet', [WalletController::class, 'index'])->name('wallet');
});

