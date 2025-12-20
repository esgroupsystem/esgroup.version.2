<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FareController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\CashierRemittanceController;
use App\Http\Controllers\Api\TripController;

Route::prefix('v1')->group(function () {

    /* ================= AUTH ================= */
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        // Logged-in user
        Route::get('/me', function (Request $request) {
            return response()->json($request->user());
        });

        // Logout
        Route::post('/logout', [AuthController::class, 'logout']);

        /* ================= POS DATA ================= */

        Route::get('/fares', [FareController::class, 'index']);
        Route::post('/fares', [FareController::class, 'store']);

        Route::post('/trips', [TripController::class, 'store']);

        Route::post('/tickets', [TicketController::class, 'store']);

        Route::post('/remittance', [CashierRemittanceController::class, 'store']);
    });
});
