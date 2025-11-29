<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\BalanceController;

Route::redirect('/', '/balance');

Route::get('/balance', [BalanceController::class, 'index'])->name('balance.index');
