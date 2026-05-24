<?php

use App\Http\Controllers\MobileController;
use Illuminate\Support\Facades\Route;

Route::post('/mobile/login', [MobileController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/mobile/context',       [MobileController::class, 'context']);
    Route::get('/mobile/trips',         [MobileController::class, 'trips']);
    Route::get('/mobile/prices',        [MobileController::class, 'prices']);
    Route::get('/mobile/transactions',  [MobileController::class, 'transactions']);
    Route::post('/mobile/transactions', [MobileController::class, 'storeTx']);
    Route::post('/mobile/expenses',     [MobileController::class, 'storeExpense']);
});
