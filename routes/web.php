<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\BalanceController;
use App\Http\Controllers\Auth\LoginController;

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
Route::redirect('/', '/balance');
Route::get('/balance', [BalanceController::class, 'index'])->name('balance.index');
});
