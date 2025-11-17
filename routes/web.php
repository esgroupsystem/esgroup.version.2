<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HR_Department\DepartmentController;
use App\Http\Controllers\HR_Department\EmployeeController;
use App\Http\Controllers\IT_Department\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [AuthController::class, 'showLogin'])->name('landing');
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
            Route::get('/it-department', 'itindex')->name('itindex');
        });

    /*
    |--------------------------------------------------------------------------
    | IT Department Routes (Only Developer/IT Officer/Safety Officer/IT Head/ Head Inspector)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Developer,Admin,IT Officer,IT Head,Safety Officer,Head Inspector'])
        ->prefix('tickets')
        ->name('tickets.')
        ->controller(TicketController::class)
        ->group(function () {

            Route::get('/job-order', 'index')->name('joborder.index');
            Route::get('/create-job-order', 'createjobordersIndex')->name('createjoborder.index');
            Route::post('/store-job-order', 'storeJoborders')->name('storejoborder.post');

            Route::get('/job-order/view/{id}', 'view')->name('joborder.view');
            Route::post('/joborder/{id}/accept', 'acceptTask')->name('joborder.accept');
            Route::post('/joborder/{id}/done', 'markAsDone')->name('joborder.done');
            Route::post('/joborder/{id}/add-note', 'addNote')->name('joborder.addnote');
            Route::post('/tickets/joborder/{id}/addfile', 'addFiles')->name('joborder.addfile');
            Route::put('/joborder/{id}/update', 'update')->name('joborder.update');
            Route::get('/export/{type}', 'export')->name('export');


            // CCTV Management for Safety Officer
            Route::get('/cctv', 'cctvindex')->name('cctv.index');
        });

    /*
    |--------------------------------------------------------------------------
    | HR Department Routes (Only HR/Admin)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:Developer,Admin,HR Officer,HR Head'])
        ->prefix('employees')
        ->name('employees.')
        ->controller(EmployeeController::class)
        ->group(function () {
            Route::get('/', 'index')->name('staff.index');
            Route::post('/store', 'store')->name('staff.store');
            Route::delete('/{employee}', 'destroy')->name('staff.destroy');

            // Employee Profile
            Route::get('/employee/{employee}', 'show')->name('staff.show');
            Route::put('/employee/{employee}', 'update')->name('update');
            Route::post('/employee/{employee}/201', 'updateAssets')->name('assets.update');

            Route::post('/{employee}/history', 'storeHistory')->name('staff.history.store');
            Route::delete('/{employee}/history/{history}', 'destroyHistory')->name('staff.history.destroy');

            Route::post('/{employee}/attachments', 'storeAttachment')->name('staff.attachments.store');
            Route::delete('/{employee}/attachments/{attachment}', 'destroyAttachment')->name('staff.attachments.destroy');

            Route::get('/{employee}/print', 'print201')->name('staff.print');

        });

    Route::middleware(['role:Developer,Admin,HR Officer,HR Head'])
        ->prefix('employees')
        ->name('employees.')
        ->controller(DepartmentController::class)
        ->group(function () {
            Route::get('/departments', 'index')->name('departments.index');
            Route::post('/departments', 'store')->name('departments.store');
            Route::post('/departments/position', 'storePosition')->name('departments.position.store');
            Route::delete('/departments/{department}', 'destroy')->name('departments.destroy');
            Route::delete('/positions/{position}', 'destroyPosition')->name('positions.destroy');
        });
});
