<?php

use App\Http\Controllers\Api\BalanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Balance operations routes
Route::prefix('balances')->group(function () {
    // Get all balance operations
    Route::get('/', [BalanceController::class, 'index']);
    
    // Calculate fees endpoints (before {balance} route to avoid conflicts)
    Route::post('/calculate-deposit-fees', [BalanceController::class, 'calculateDepositFees']);
    Route::post('/calculate-withdrawal-fees', [BalanceController::class, 'calculateWithdrawalFees']);
    
    // Create operations
    Route::post('/deposit', [BalanceController::class, 'storeDeposit']);
    Route::post('/withdraw', [BalanceController::class, 'storeWithdrawal']);
    
    // Get specific balance operation details (using route model binding)
    Route::get('/{balance}', [BalanceController::class, 'show']);
    
    // Complete a balance operation (using route model binding)
    Route::patch('/{balance}/complete', [BalanceController::class, 'complete']);
});

