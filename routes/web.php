<?php

use App\Http\Controllers\Accounting\AccountingController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BusDetailController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HR_Department\ConductorLeaveController;
use App\Http\Controllers\HR_Department\DepartmentController;
use App\Http\Controllers\HR_Department\DriverLeaveController;
use App\Http\Controllers\HR_Department\EmployeeController;
use App\Http\Controllers\HR_Department\EmployeeLeaveController;
use App\Http\Controllers\HR_Department\HRDashboardController;
use App\Http\Controllers\HR_Department\HrOffenseController;
use App\Http\Controllers\HR_Department\MirasolBiometricsLogController;
use App\Http\Controllers\IT\ItInventoryItemController;
use App\Http\Controllers\IT_Department\CctvController;
use App\Http\Controllers\IT_Department\TicketController;
use App\Http\Controllers\Maintenance\CategoryController;
use App\Http\Controllers\Maintenance\ItemsController;
use App\Http\Controllers\Maintenance\PartsOutController;
use App\Http\Controllers\Maintenance\PurchaseReceiveController;
use App\Http\Controllers\Maintenance\ReceivingController;
use App\Http\Controllers\Maintenance\RequestController;
use App\Http\Controllers\Maintenance\StockTransferController;
use App\Http\Controllers\Payroll\AttendanceSummaryController;
use App\Http\Controllers\Payroll\EmployeePlottingScheduleController;
use App\Http\Controllers\Payroll\HolidayController;
use App\Http\Controllers\Payroll\ManualBiometricsEncodingController;
use App\Http\Controllers\Payroll\PayrollAttendanceAdjustmentController;
use App\Http\Controllers\Payroll\PayrollController;
use App\Http\Controllers\Payroll\PayrollEmployeeSalaryController;
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

Route::get('/loaderio-8ae07cb33aeb9a3b6e150082fc966226.txt', function () {
    return response('loaderio-8ae07cb33aeb9a3b6e150082fc966226', 200)
        ->header('Content-Type', 'text/plain');
});

/*
|--------------------------------------------------------------------------
| Authentication (Public)
|--------------------------------------------------------------------------
*/
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')
        ->middleware('throttle:5,1')
        ->name('login.post');
    Route::post('/register', 'register')
        ->middleware('throttle:3,1')
        ->name('register.post');
    Route::post('/logout', 'logout')->name('logout');
    Route::get('/lockscreen', 'showLockscreen')->name('lockscreen.show');
    Route::post('/unlock', 'unlock')
        ->middleware('throttle:5,1')
        ->name('lockscreen.unlock');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', ForceLockscreen::class])->group(function () {

    Route::get('/performance/dashboard', function () {
        return response()->json(['module' => 'dashboard', 'status' => 'ok']);
    });

    Route::get('/performance/it', function () {
        return response()->json(['module' => 'it', 'status' => 'ok']);
    });

    Route::get('/performance/hr', function () {
        return response()->json(['module' => 'hr', 'status' => 'ok']);
    });

    Route::get('/performance/maintenance', function () {
        return response()->json(['module' => 'maintenance', 'status' => 'ok']);
    });

    Route::get('/performance/payroll', function () {
        return response()->json(['module' => 'payroll', 'status' => 'ok']);
    });

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
    Route::middleware(['auth', 'role:Developer,IT Officer,IT Head,Safety Officer,Head Inspector,Operation Manager'])
        ->prefix('tickets')->name('tickets.')
        ->controller(TicketController::class)->group(function () {

            Route::get('/job-order', 'index')->name('joborder.index');
            Route::get('/create-job-order', 'createjobordersIndex')->name('createjoborder.index');
            Route::post('/store-job-order', 'storeJoborders')->name('storejoborder.post');

            Route::get('/job-order/view/{id}', 'view')->name('joborder.view');
            Route::put('/joborder/{id}/update', 'update')->name('joborder.update');
            Route::delete('/joborder/{id}', 'destroy')->name('joborder.delete');

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

    Route::middleware(['auth', 'role:Developer,IT Officer,IT Head,Safety Officer,Head Inspector,Operation Manager'])
        ->prefix('it-inventory')->name('it-inventory.')
        ->controller(ItInventoryItemController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->name('edit');
            Route::put('/{id}', 'update')->name('update');
            Route::delete('/it-inventory/{id}', 'destroy')->name('destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | CCTV (Job Orders)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'role:Developer,IT Officer,IT Head,Safety Officer,Head Inspector,Operation Manager'])
        ->prefix('concern')->name('concern.')
        ->controller(CctvController::class)->group(function () {

            Route::get('/cctv', 'index')->name('cctv.index');
            Route::post('/cctv', 'store')->name('cctv.store');
            Route::get('/cctv/view/{id}', 'view')->name('cctv.view');
            Route::put('/cctv/{id}', 'update')->name('cctv.update');
            Route::delete('/cctv/{id}', 'destroy')->name('cctv.destroy');

            Route::post('/cctv/{id}/accept', 'acceptTask')->name('cctv.accept');
            Route::post('/cctv/{id}/done', 'markAsDone')->name('cctv.done');
            Route::post('/cctv/{id}/note', 'addNote')->name('cctv.addnote');
            Route::post('/cctv/{id}/files', 'addFiles')->name('cctv.addfile');

            Route::get('/export/{type}', 'export')->name('export');
            Route::get('/cctv/bus-status', 'busStatus')->name('bus-status');

            Route::get('/cctv/bus-status/{bodyNumber}', 'busStatusShow')->name('bus-status.show');
        });

    Route::resource('cctv-parts', CctvController::class);

    /*
    |--------------------------------------------------------------------------
    | HR Dashboard
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head,Operation Manager'])
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
    Route::middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head,Operation Manager'])
        ->prefix('employees')->name('employees.')->group(function () {

            // Employee Records
            Route::controller(EmployeeController::class)->group(function () {
                Route::get('/', 'index')->name('staff.index');
                Route::post('/store', 'store')->name('staff.store');
                Route::get('/employee/check-permanent-id', 'checkPermanentId')->name('staff.checkPermanentId');

                Route::delete('/{employee}', 'destroy')->name('staff.destroy');
                Route::get('/employee/{employee}', 'show')->name('staff.show');
                Route::put('/employee/{employee}', 'update')->name('update');
                Route::post('/employee/{employee}/201', 'updateAssets')->name('assets.update');
                Route::post('/{employee}/history', 'storeHistory')->name('staff.history.store');
                Route::delete('/{employee}/history/{history}', 'destroyHistory')->name('staff.history.destroy');
                Route::post('/{employee}/attachments', 'storeAttachment')->name('staff.attachments.store');
                Route::delete('/{employee}/attachments/{attachment}', 'destroyAttachment')->name('staff.attachments.destroy');
                Route::put('/employees/{employee}/status-details', 'updateStatusDetails')->name('status-details.update');

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
    | Policy Management
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head'])
        ->prefix('violation')->name('violation.')->group(function () {

            Route::controller(HrOffenseController::class)->group(function () {
                Route::get('/offenses', 'index')->name('offenses.index');
                Route::post('/offenses/store', 'store')->name('offenses.store');
            });

        });

    /*
    |--------------------------------------------------------------------------
    | Leave Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('driver-leave')->middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head,Operation Manager'])
        ->name('driver-leave.')->controller(DriverLeaveController::class)->group(function () {
            Route::get('driver', 'index')->name('driver.index');
            Route::get('driver/create', 'create')->name('driver.create');
            Route::post('driver/store', 'store')->name('driver.store');
            Route::get('driver/{leave}/edit', 'edit')->name('driver.edit');
            Route::put('driver/{leave}', 'update')->name('driver.update');
            Route::post('{leave}/action', 'action')->name('driver.action');
        });

    Route::prefix('conductor-leave')->middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head,Operation Manager'])
        ->name('conductor-leave.')->controller(ConductorLeaveController::class)->group(function () {
            Route::get('conductor', 'index')->name('conductor.index');
            Route::get('conductor/create', 'create')->name('conductor.create');
            Route::post('conductor/store', 'store')->name('conductor.store');
            Route::get('conductor/{leave}/edit', 'edit')->name('conductor.edit');
            Route::put('conductor/{leave}', 'update')->name('conductor.update');
            Route::post('/{leave}/action', 'action')->name('conductor.action');
        });

    Route::prefix('employee-leave')->middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head,Operation Manager'])
        ->name('employee-leave.')->controller(EmployeeLeaveController::class)->group(function () {
            Route::get('employee', 'index')->name('employee.index');
            Route::get('employee/create', 'create')->name('employee.create');
            Route::post('employee/store', 'store')->name('employee.store');
            Route::get('employee/{leave}/edit', 'edit')->name('employee.edit');
            Route::put('employee/{leave}', 'update')->name('employee.update');
            Route::post('{leave}/action', 'action')->name('employee.action');
        });

    /*
    |--------------------------------------------------------------------------
    | Holiday Management
    |-------------------------------------------------------------------------- */

    Route::middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head'])->group(function () {
        Route::resource('holidays', HolidayController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | Plotting Schedule
    |-------------------------------------------------------------------------- */

    Route::prefix('payroll-plotting')->middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head'])
        ->name('payroll-plotting.')->controller(EmployeePlottingScheduleController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/save-monthly', 'saveMonthly')->name('save-monthly');
            Route::post('/quick-fill', 'quickFill')->name('quick-fill');
            Route::get('/search-suggestions', 'searchSuggestions')->name('search-suggestions');
        });

    /*
    |--------------------------------------------------------------------------
    | Manual Biometrics Encoding
    |-------------------------------------------------------------------------- */

    Route::prefix('manual-biometrics')->middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head'])
        ->name('manual-biometrics.')->controller(ManualBiometricsEncodingController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/search-employees', 'searchEmployees')->name('search-employees');
            Route::post('/', 'store')->name('store');
        });

    /*
    |--------------------------------------------------------------------------
    | Payroll Attendance Adjustments
    |--------------------------------------------------------------------------
    */
    Route::prefix('payroll-attendance-adjustments')->middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head'])
        ->name('payroll-attendance-adjustments.')->controller(PayrollAttendanceAdjustmentController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{payrollAttendanceAdjustment}/edit', 'edit')->name('edit');
            Route::put('/{payrollAttendanceAdjustment}', 'update')->name('update');
            Route::delete('/{payrollAttendanceAdjustment}', 'destroy')->name('destroy');
        });

    Route::prefix('attendance-summary')->middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head'])
        ->name('attendance-summary.')->controller(AttendanceSummaryController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/create', 'rebuild')->name('rebuild');
        });

    Route::middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head'])
        ->prefix('payroll')
        ->group(function () {
            Route::get('employee-salaries/sync', [PayrollEmployeeSalaryController::class, 'syncFromBiometrics'])
                ->name('payroll-employee-salaries.sync');

            Route::resource('employee-salaries', PayrollEmployeeSalaryController::class)
                ->except(['show'])
                ->parameters(['employee-salaries' => 'payrollEmployeeSalary'])
                ->names('payroll-employee-salaries');
        });

    Route::prefix('payroll')->middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head'])
        ->name('payroll.')->controller(PayrollController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');

            Route::get('/payrolls/{payroll}/items/{item}', 'showItem')
                ->whereNumber('payroll')
                ->name('items.show');

            Route::post('/payrolls/{payroll}/finalize', 'finalize')
                ->whereNumber('payroll')
                ->name('finalize');

            Route::get('/payrolls/{payroll}/export/excel', 'exportExcel')
                ->whereNumber('payroll')
                ->name('export.excel');

            Route::get('/payrolls/{payroll}/export/pdf', 'exportPdf')
                ->whereNumber('payroll')
                ->name('export.pdf');

            Route::get('/{payroll}', 'show')
                ->whereNumber('payroll')
                ->name('show');

            Route::delete('/{payroll}', 'destroy')
                ->whereNumber('payroll')
                ->name('destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Claims Management
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'role:Developer,IT Head,HR Officer,HR Head,Operation Manager'])
        ->prefix('claims')->name('claims.')
        ->controller(ClaimController::class)
        ->group(function () {

            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::put('/{claim}', 'update')->name('update');
            Route::delete('/{claim}', 'destroy')->name('destroy');
        });
    /*
    |--------------------------------------------------------------------------
    | Mirasol Biometrics Management
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'role:Developer,IT Officer,IT Head,HR Officer,HR Head'])
        ->prefix('mirasol-logs')->name('mirasol-logs.')
        ->controller(MirasolBiometricsLogController::class)->group(function () {

            Route::get('/', 'index')->name('index');

            // ✅ NEW: modal progress sync
            Route::post('/sync-start', 'startSync')->name('sync-start');
            Route::get('/sync-status', 'syncStatus')->name('sync-status');

            // (optional) keep old sync route if you still want normal submit
            // Route::post('/sync', 'sync')->name('sync');
        });

    /*
    |--------------------------------------------------------------------------
    | User Management & Roles
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth', 'role:Developer,IT Head'])->group(function () {

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
    Route::middleware(['auth', 'role:Developer,IT Head,Operation Manager,Maintenance Engineer,Maintenance Encoder'])->group(function () {

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
            Route::delete('/destroy/{id}', 'destroy')->name('destroy');
            Route::get('/dashboard', 'dashboard')->name('dashboard');

        });

        Route::prefix('received')->name('received.')->controller(PurchaseReceiveController::class)->group(function () {
            Route::get('po/receiving', 'index')->name('index');
            Route::get('po/receiving/{id}', 'details')->name('details');
            Route::post('po/item/{id}/receive', 'receive')->name('received');
        });

        Route::prefix('receivings')->name('receivings.')->controller(ReceivingController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::get('/search-products', 'searchProducts')->name('search-products');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::post('/{receiving}/items/{item}/rollback', 'rollbackItem')->name('rollback');
        });

        Route::prefix('parts-out')->name('parts-out.')->controller(PartsOutController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::get('/search-products', 'searchProducts')->name('search-products');
            Route::post('/', 'store')->name('store');
            Route::get('/{partsOut}', 'show')->name('show');
            Route::get('/{partsOut}/edit', 'edit')->name('edit');
            Route::put('/{partsOut}', 'update')->name('update');
            Route::patch('/{partsOut}/cancel', 'cancel')->name('cancel');
            Route::get('/{partsOut}/print', 'print')->name('print');
            Route::post('/parts-out/{partsOut}/rollback', 'rollback')->name('rollback');
        });

        Route::prefix('buses')->name('buses.')->controller(BusDetailController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{busDetail}', 'show')->name('show');
            Route::get('/{busDetail}/maintenance-history', 'maintenanceHistory')->name('maintenance-history');
        });
    });

    Route::middleware(['auth', 'role:Developer,IT Head,Maintenance Head,Maintenance Engineer,Maintenance Encoder'])->group(function () {
        Route::get('stock-transfers/search-products', [StockTransferController::class, 'searchProducts'])
            ->name('stock-transfers.search-products');

        Route::resource('stock-transfers', StockTransferController::class)
            ->only(['index', 'create', 'store', 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Accounting - Purchase Orders
    |--------------------------------------------------------------------------
    */
    Route::model('order', App\Models\PurchaseOrder::class);

    Route::middleware(['auth', 'role:Developer,IT Head,Operation Manager,Maintenance Engineer,Maintenance Encoder'])
        ->prefix('purchase')->name('purchase.')
        ->controller(AccountingController::class)->group(function () {
            Route::get('/index', 'index')->name('index');
            Route::post('/update/{order}', 'update')->name('update');
        });

});
