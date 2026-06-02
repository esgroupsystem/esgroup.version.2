<?php

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\BusController;
use App\Http\Controllers\Api\OdometerController;
use App\Http\Controllers\BiometricsViewController;
use App\Http\Controllers\BiometricsWebhookController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [ApiAuthController::class, 'login'])->middleware('throttle:5,1');

// Biometrics webhook
Route::post('/webhook/crosschex', [BiometricsWebhookController::class, 'receive'])
    ->name('webhook.crosschex')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    
Route::get('/biometrics/latest', [BiometricsViewController::class, 'latest'])->name('biometrics.latest');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/buses', [BusController::class, 'index']);
    Route::get('/bus/{busDetail}/last-odometer', [OdometerController::class, 'lastOdometer']);
    Route::post('/odometer-submit', [OdometerController::class, 'store']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);

});
