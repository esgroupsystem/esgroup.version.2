<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IT_Department\TicketController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Landing page (login/register modal)
Route::get('/', [AuthController::class, 'showLogin'])->name('landing');
// ✅ Login page route (REQUIRED by Laravel)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');


// Authentication Routes
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login')->middleware('throttle:5,1')->name('login.post');
    Route::post('/register', 'register')->name('register.post');
    Route::post('/logout', 'logout')->name('logout');
});


/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard (All Authenticated Users)
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')
        ->name('dashboard.')
        ->controller(DashboardController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/analytics', 'analyticsindex')->name('analytics');
            Route::get('/crm', 'crmindex')->name('crm');
        });


    /*
    |--------------------------------------------------------------------------
    | IT Department Routes (Only HR/Admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Developer,Admin,IT Officer'])
        ->prefix('tickets')
        ->name('tickets.')
        ->controller(TicketController::class)
        ->group(function () {
            Route::get('/job-order', 'index')->name('joborder.index');
            Route::get('/create-job-order', 'createjobordersIndex')->name('createjoborder.index');
            Route::post('/store-job-order', 'storeJoborders')->name('storejoborder.post');

            Route::get('/cctv', 'cctvindex')->name('cctv.index');
        });
});
