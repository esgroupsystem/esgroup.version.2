<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\BusController;
use App\Http\Controllers\Api\OdometerController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [ApiAuthController::class, 'login'])
    ->middleware('throttle:api-login')
    ->name('api.login');

Route::middleware(['auth:sanctum', 'abilities:api:access', 'throttle:api'])->group(function () {
    Route::get('/buses', [BusController::class, 'index'])->name('api.buses.index');
    Route::get('/bus/{busDetail}/last-odometer', [OdometerController::class, 'lastOdometer'])->name('api.buses.last-odometer');
    Route::post('/odometer-submit', [OdometerController::class, 'store'])->name('api.odometer.store');
    Route::post('/logout', [ApiAuthController::class, 'logout'])->name('api.logout');
});
