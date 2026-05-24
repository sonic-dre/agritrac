<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProduceTypeController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth routes (unauthenticated)
Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// All protected routes require authentication
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard data API
    Route::prefix('api/dashboard')->group(function () {
        Route::get('/ov',  [DashboardController::class, 'overview']);
        Route::get('/tr',  [DashboardController::class, 'trips']);
        Route::get('/pr',  [DashboardController::class, 'prices']);
        Route::get('/fc',  [DashboardController::class, 'forecast']);
        Route::get('/ac',  [DashboardController::class, 'accounting']);
        Route::get('/ex',  [DashboardController::class, 'expenses']);
        Route::get('/hi',  [DashboardController::class, 'history']);
        Route::get('/sy',  [DashboardController::class, 'sync']);
        Route::get('/st',  [DashboardController::class, 'stock']);
        Route::get('/um',  [UserController::class, 'index']);
        Route::get('/pu',  [DashboardController::class, 'produceUnits']);
        Route::get('/mp',  [DashboardController::class, 'fieldMap']);
    });

    // Trips CRUD
    Route::post('/trips',         [TripController::class, 'store']);
    Route::put('/trips/{trip}',   [TripController::class, 'update']);
    Route::delete('/trips/{trip}',[TripController::class, 'destroy']);

    // Transactions CRUD
    Route::post('/transactions',                  [TransactionController::class, 'store']);
    Route::delete('/transactions/{transaction}',  [TransactionController::class, 'destroy']);

    // Expenses CRUD
    Route::post('/expenses',              [ExpenseController::class, 'store']);
    Route::put('/expenses/{expense}',     [ExpenseController::class, 'update']);
    Route::delete('/expenses/{expense}',  [ExpenseController::class, 'destroy']);

    // Price update
    Route::put('/prices/{produceType}', [PriceController::class, 'update']);

    // Sync
    Route::post('/sync/force', [SyncController::class, 'forceSync']);

    // CSV Export
    Route::get('/export/{page}', [ExportController::class, 'download']);

    // User management (admin only — controller enforces this)
    Route::post('/users',          [UserController::class, 'store']);
    Route::put('/users/{user}',    [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // Mobile agent management (manager only — controller enforces this)
    Route::get('/api/dashboard/ma',           [DashboardController::class, 'mobileAgents']);
    Route::post('/agents',                    [AgentController::class, 'store']);
    Route::put('/agents/{agent}',             [AgentController::class, 'update']);
    Route::patch('/agents/{agent}/toggle',    [AgentController::class, 'toggleActive']);
    Route::delete('/agents/{agent}',          [AgentController::class, 'destroy']);

    // Produce types CRUD
    Route::post('/produce-types',                   [ProduceTypeController::class, 'store']);
    Route::put('/produce-types/{produceType}',      [ProduceTypeController::class, 'update']);
    Route::delete('/produce-types/{produceType}',   [ProduceTypeController::class, 'destroy']);

    // Units CRUD
    Route::post('/units',          [UnitController::class, 'store']);
    Route::put('/units/{unit}',    [UnitController::class, 'update']);
    Route::delete('/units/{unit}', [UnitController::class, 'destroy']);
});
