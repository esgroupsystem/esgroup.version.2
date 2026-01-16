<?php

use App\Http\Controllers\Accounting\AccountingController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HR_Department\ConductorLeaveController;
use App\Http\Controllers\HR_Department\DepartmentController;
use App\Http\Controllers\HR_Department\DriverLeaveController;
use App\Http\Controllers\HR_Department\EmployeeController;
use App\Http\Controllers\HR_Department\HRDashboardController;
use App\Http\Controllers\IT_Department\TicketController;
use App\Http\Controllers\Maintenance\CategoryController;
use App\Http\Controllers\Maintenance\ItemsController;
use App\Http\Controllers\Maintenance\PurchaseReceiveController;
use App\Http\Controllers\Maintenance\RequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserManagementController;
use App\Http\Middleware\ForceLockscreen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing Route
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('lockscreen.show')
        : redirect()->route('login');
})->name('landing');

/*
|--------------------------------------------------------------------------
| Authentication (Public)
|--------------------------------------------------------------------------
*/
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->middleware('throttle:5,1')->name('login.post');
    Route::post('/register', 'register')->name('register.post');
    Route::post('/logout', 'logout')->name('logout');
    Route::get('/lockscreen', 'showLockscreen')->name('lockscreen.show');
    Route::post('/unlock', 'unlock')->name('lockscreen.unlock');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', ForceLockscreen::class])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->name('dashboard.')->controller(DashboardController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/analytics', 'analyticsindex')->name('analytics');
        Route::get('/crm', 'crmindex')->name('crm');
        Route::get('/it-department', 'itindex')->name('itindex');
    });

    /*
    |--------------------------------------------------------------------------
    | Change Password
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->name('auth.')->controller(AuthController::class)->group(function () {
        Route::get('/change-password', 'changePasswordForm')->name('change.password.form');
        Route::post('/change-password', 'changePasswordUpdate')->name('change.password.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Tickets (Job Orders)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Developer,IT Officer,IT Head,Safety Officer,Head Inspector,Operation Manager'])
        ->prefix('tickets')->name('tickets.')
        ->controller(TicketController::class)->group(function () {

            Route::get('/job-order', 'index')->name('joborder.index');
            Route::get('/create-job-order', 'createjobordersIndex')->name('createjoborder.index');
            Route::post('/store-job-order', 'storeJoborders')->name('storejoborder.post');

            Route::get('/job-order/view/{id}', 'view')->name('joborder.view');
            Route::put('/joborder/{id}/update', 'update')->name('joborder.update');

            Route::post('/joborder/{id}/accept', 'acceptTask')->name('joborder.accept');
            Route::post('/joborder/{id}/done', 'markAsDone')->name('joborder.done');

            Route::post('/joborder/{id}/add-note', 'addNote')->name('joborder.addnote');
            Route::post('/joborder/{id}/addfile', 'addFiles')->name('joborder.addfile');

            Route::get('/export/{type}', 'export')->name('export');
            Route::get('/cctv', 'cctvindex')->name('cctv.index');
            Route::get('joborder/{id}/print', 'print')->name('joborder.print');

            Route::post('/joborder/{id}/approve', 'approve')->name('approve');
            Route::post('/joborder/{id}/disapprove', 'disapprove')->name('disapprove');
        });

    /*
    |--------------------------------------------------------------------------
    | HR Dashboard
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Developer,IT Head,HR Officer,HR Head,Operation Manager'])
        ->prefix('hr')->name('hr.')
        ->controller(HRDashboardController::class)->group(function () {

            Route::get('/dashboard', 'index')->name('dashboard');
            Route::get('/dashboard/chart/employees-by-dept', 'employeesByDeptChart')->name('dashboard.chart.employees_by_dept');
            Route::get('/dashboard/chart/leaves-by-type', 'leavesByTypeChart')->name('dashboard.chart.leaves_by_type');
        });

    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Developer,IT Head,HR Officer,HR Head,Operation Manager'])
        ->prefix('employees')->name('employees.')->group(function () {

            // Employee Records
            Route::controller(EmployeeController::class)->group(function () {
                Route::get('/', 'index')->name('staff.index');
                Route::post('/store', 'store')->name('staff.store');
                Route::delete('/{employee}', 'destroy')->name('staff.destroy');
                Route::get('/employee/{employee}', 'show')->name('staff.show');
                Route::put('/employee/{employee}', 'update')->name('update');
                Route::post('/employee/{employee}/201', 'updateAssets')->name('assets.update');
                Route::post('/{employee}/history', 'storeHistory')->name('staff.history.store');
                Route::delete('/{employee}/history/{history}', 'destroyHistory')->name('staff.history.destroy');
                Route::post('/{employee}/attachments', 'storeAttachment')->name('staff.attachments.store');
                Route::delete('/{employee}/attachments/{attachment}', 'destroyAttachment')->name('staff.attachments.destroy');

                Route::get('/{employee}/print', 'print201')->name('staff.print');
                Route::get('/departments/{id}/positions', 'getPositions')->name('positions');
            });

            // Departments
            Route::controller(DepartmentController::class)->group(function () {
                Route::get('/departments', 'index')->name('departments.index');
                Route::post('/departments', 'store')->name('departments.store');
                Route::post('/departments/position', 'storePosition')->name('departments.position.store');
                Route::delete('/departments/{department}', 'destroy')->name('departments.destroy');
                Route::delete('/positions/{position}', 'destroyPosition')->name('positions.destroy');
                Route::get('/departments/{id}/positions', 'positions')->name('departments.positions');
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Leave Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('driver-leave')->middleware(['role:Developer,IT Head,HR Officer,HR Head,Operation Manager'])
        ->name('driver-leave.')->controller(DriverLeaveController::class)->group(function () {
            Route::get('driver', 'index')->name('driver.index');
            Route::get('driver/create', 'create')->name('driver.create');
            Route::post('driver/store', 'store')->name('driver.store');
            Route::get('driver/{leave}/edit', 'edit')->name('driver.edit');
            Route::put('driver/{leave}', 'update')->name('driver.update');
            Route::post('{leave}/action', 'action')->name('driver.action');
        });

    Route::prefix('conductor-leave')->middleware(['role:Developer,IT Head,HR Officer,HR Head,Operation Manager'])
        ->name('conductor-leave.')->controller(ConductorLeaveController::class)->group(function () {
            Route::get('conductor', 'index')->name('conductor.index');
            Route::get('conductor/create', 'create')->name('conductor.create');
            Route::post('conductor/store', 'store')->name('conductor.store');
            Route::get('/conductor-leave/{id}/edit', 'edit')->name('conductor.edit');
            Route::put('/conductor-leave/{leave}', 'update')->name('conductor.update');
            Route::post('/{leave}/action', 'action')->name('conductor.action');
        });

    /*
    |--------------------------------------------------------------------------
    | User Management & Roles
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Developer,IT Head,Operation Manager'])->group(function () {

        // Authentication (User Management)
        Route::prefix('authentication')->name('authentication.')
            ->controller(UserManagementController::class)->group(function () {
                Route::get('/users', 'index')->name('users.index');
                Route::get('/users/create', 'create')->name('users.create');
                Route::post('/users/store', 'store')->name('users.store');
                Route::get('/users/edit/{id}', 'edit')->name('users.edit');
                Route::post('/users/update/{id}', 'update')->name('users.update');
                Route::post('/users/reset-password/{id}', 'resetPassword')->name('users.reset.password');
                Route::get('/users/status/{id}', 'status')->name('users.status');
            });

        // Roles
        Route::prefix('roles')->name('roles.')->controller(RoleController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/status/{id}', 'destroy')->name('destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Maintenance (Request, Category, Items)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Developer,IT Head,Operation Manager,Maintenance Engineer'])->group(function () {

        Route::prefix('request')->name('request.')->controller(RequestController::class)->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::put('/update/{id}', 'update')->name('update');
            Route::get('/status/{id}', 'destroy')->name('destroy');
        });

        Route::prefix('category')->name('category.')->controller(CategoryController::class)->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/status/{id}', 'destroy')->name('destroy');
        });

        Route::prefix('items')->name('items.')->controller(ItemsController::class)->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/status/{id}', 'destroy')->name('destroy');

        });

        Route::prefix('received')->name('received.')->controller(PurchaseReceiveController::class)->group(function () {
            Route::get('po/receiving', 'index')->name('index');
            Route::get('po/receiving/{id}', 'details')->name('details');
            Route::post('po/item/{id}/receive', 'receive')->name('received');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Accounting - Purchase Orders
    |--------------------------------------------------------------------------
    */
    Route::model('order', App\Models\PurchaseOrder::class);

    Route::middleware(['role:Developer,IT Head,Operation Manager,Maintenance Engineer'])
        ->prefix('purchase')->name('purchase.')
        ->controller(AccountingController::class)->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::post('/update/{order}', 'update')->name('update');
        });

});
