<?php

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
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return redirect()->route('lockscreen.show');
})->name('landing');

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/lockscreen', [AuthController::class, 'showLockscreen'])->name('lockscreen.show');
Route::post('/unlock', [AuthController::class, 'unlock'])->name('lockscreen.unlock');

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', ForceLockscreen::class])->group(function () {

    Route::prefix('dashboard')
        ->name('dashboard.')
        ->controller(DashboardController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/analytics', 'analyticsindex')->name('analytics');
            Route::get('/crm', 'crmindex')->name('crm');
            Route::get('/it-department', 'itindex')->name('itindex');

        });

    Route::prefix('auth')
        ->name('auth.')
        ->controller(AuthController::class)
        ->group(function () {
            Route::get('/change-password', 'changePasswordForm')->name('change.password.form');
            Route::post('/change-password', 'changePasswordUpdate')->name('change.password.update');

        });

    Route::middleware(['role:Developer,IT Officer,IT Head,Safety Officer,Head Inspector'])
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
            Route::get('/cctv', 'cctvindex')->name('cctv.index');
        });

    Route::middleware(['role:Developer,IT Head,HR Officer,HR Head'])
        ->prefix('hr')
        ->name('hr.')
        ->controller(HRDashboardController::class)
        ->group(function () {
            Route::get('dashboard', 'index')->name('dashboard');
            Route::get('dashboard/chart/employees-by-dept', 'employeesByDeptChart')->name('dashboard.chart.employees_by_dept');
            Route::get('dashboard/chart/leaves-by-type', 'leavesByTypeChart')->name('dashboard.chart.leaves_by_type');
        });

    Route::middleware(['role:Developer,IT Head,HR Officer,HR Head'])
        ->prefix('employees')
        ->name('employees.')
        ->controller(EmployeeController::class)
        ->group(function () {
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

    Route::middleware(['role:Developer,IT Head,HR Officer,HR Head'])
        ->prefix('employees')
        ->name('employees.')
        ->controller(DepartmentController::class)
        ->group(function () {
            Route::get('/departments', 'index')->name('departments.index');
            Route::post('/departments', 'store')->name('departments.store');
            Route::post('/departments/position', 'storePosition')->name('departments.position.store');
            Route::delete('/departments/{department}', 'destroy')->name('departments.destroy');
            Route::delete('/positions/{position}', 'destroyPosition')->name('positions.destroy');
            Route::get('/departments/{id}/positions', 'positions')->name('departments.positions');
        });

    Route::middleware(['role:Developer,IT Head,HR Officer,HR Head'])
        ->prefix('driver-leave')
        ->name('driver-leave.')
        ->controller(DriverLeaveController::class)
        ->group(function () {
            Route::get('driver', 'index')->name('driver.index');
            Route::get('driver/create', 'create')->name('driver.create');
            Route::post('driver/store', 'store')->name('driver.store');
            Route::get('driver/{leave}/edit', 'edit')->name('driver.edit');
            Route::put('driver/{leave}', 'update')->name('driver.update');
            Route::post('{leave}/action', 'action')->name('driver.action');
        });

    Route::middleware(['role:Developer,IT Head,HR Officer,HR Head'])
        ->prefix('conductor-leave')
        ->name('conductor-leave.')
        ->controller(ConductorLeaveController::class)
        ->group(function () {
            Route::get('conductor', 'index')->name('conductor.index');
            Route::get('conductor/create', 'create')->name('conductor.create');
            Route::post('conductor/store', 'store')->name('conductor.store');
            Route::get('/conductor-leave/{id}/edit', 'edit')->name('conductor.edit');
            Route::put('/conductor-leave/{leave}', 'update')->name('conductor.update');
            Route::post('/{leave}/action', 'action')->name('conductor.action');
        });

    Route::middleware(['role:Developer,IT Head'])
        ->prefix('authentication')
        ->name('authentication.')
        ->controller(UserManagementController::class)
        ->group(function () {
            Route::get('/users', 'index')->name('users.index');
            Route::get('/users/create', 'create')->name('users.create');
            Route::post('/users/store', 'store')->name('users.store');
            Route::get('/users/edit/{id}', 'edit')->name('users.edit');
            Route::post('/users/update/{id}', 'update')->name('users.update');
            Route::post('/users/reset-password/{id}', 'resetPassword')->name('users.reset.password');
            Route::get('/users/status/{id}', 'status')->name('users.status');
        });

    Route::middleware(['role:Developer,IT Head'])
        ->prefix('roles')
        ->name('roles.')
        ->controller(RoleController::class)
        ->group(function () {
            Route::get('/roles', 'index')->name('index');
            Route::post('/roles/store', 'store')->name('store');
            Route::get('/roles/edit/{id}', 'edit')->name('edit');
            Route::post('/roles/update/{id}', 'update')->name('update');
            Route::get('/roles/status/{id}', 'destroy')->name('destroy');
        });

    Route::middleware(['role:Developer,IT Head'])
        ->prefix('request')
        ->name('request.')
        ->controller(RequestController::class)
        ->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::put('/update/{id}', 'update')->name('update');
            Route::get('/status/{id}', 'destroy')->name('destroy');
        });

    Route::middleware(['role:Developer,IT Head'])
        ->prefix('category')
        ->name('category.')
        ->controller(CategoryController::class)
        ->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/status/{id}', 'destroy')->name('destroy');
        });

    Route::middleware(['role:Developer,IT Head'])
        ->prefix('items')
        ->name('items.')
        ->controller(ItemsController::class)
        ->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update/{id}', 'update')->name('update');
            Route::get('/status/{id}', 'destroy')->name('destroy');
        });

});
