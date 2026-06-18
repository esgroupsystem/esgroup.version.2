<?php

use App\Http\Controllers\Accounting\AccountingController;
use App\Http\Controllers\AllBusController;
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
use App\Http\Controllers\Maintenance\OdometerReportController;
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

/*
|--------------------------------------------------------------------------
| Authentication (Public)
|--------------------------------------------------------------------------
*/
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')
        ->name('login.post');
    Route::post('/register', 'register')
        ->name('register.post');
    Route::post('/logout', 'logout')->name('logout');
    Route::get('/lockscreen', 'showLockscreen')->name('lockscreen.show');
    Route::post('/unlock', 'unlock')
        ->name('lockscreen.unlock');
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
    Route::prefix('dashboard')
        ->middleware(['auth'])
        ->name('dashboard.')
        ->controller(DashboardController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->name('index');

            Route::get('/analytics', 'analyticsindex')
                ->middleware('permission:dashboard.analytics')
                ->name('analytics');

            Route::get('/crm', 'crmindex')
                ->middleware('permission:dashboard.crm')
                ->name('crm');

            Route::get('/it-department', 'itindex')
                ->middleware('permission:dashboard.it')
                ->name('itindex');

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
    Route::middleware(['auth'])
        ->prefix('tickets')
        ->name('tickets.')
        ->controller(TicketController::class)
        ->group(function () {

            Route::get('/job-order', 'index')
                ->middleware('permission:tickets.view')
                ->name('joborder.index');

            Route::get('/create-job-order', 'createjobordersIndex')
                ->middleware('permission:tickets.create')
                ->name('createjoborder.index');

            Route::post('/store-job-order', 'storeJoborders')
                ->middleware('permission:tickets.create')
                ->name('storejoborder.post');

            Route::get('/job-order/view/{id}', 'view')
                ->middleware('permission:tickets.view')
                ->name('joborder.view');

            Route::put('/joborder/{id}/update', 'update')
                ->middleware('permission:tickets.update')
                ->name('joborder.update');

            Route::delete('/joborder/{id}', 'destroy')
                ->middleware('permission:tickets.delete')
                ->name('joborder.delete');

            Route::post('/joborder/{id}/accept', 'acceptTask')
                ->middleware('permission:tickets.update')
                ->name('joborder.accept');

            Route::post('/joborder/{id}/done', 'markAsDone')
                ->middleware('permission:tickets.update')
                ->name('joborder.done');

            Route::post('/joborder/{id}/add-note', 'addNote')
                ->middleware('permission:tickets.update')
                ->name('joborder.addnote');

            Route::post('/joborder/{id}/addfile', 'addFiles')
                ->middleware('permission:tickets.update')
                ->name('joborder.addfile');

            Route::get('/export/{type}', 'export')
                ->middleware('permission:tickets.export')
                ->name('export');

            Route::get('/cctv', 'cctvindex')
                ->middleware('permission:tickets.view')
                ->name('cctv.index');

            Route::get('joborder/{id}/print', 'print')
                ->middleware('permission:tickets.view')
                ->name('joborder.print');

            Route::post('/joborder/{id}/approve', 'approve')
                ->middleware('permission:tickets.approve')
                ->name('approve');

            Route::post('/joborder/{id}/disapprove', 'disapprove')
                ->middleware('permission:tickets.approve')
                ->name('disapprove');
        });

    Route::middleware(['auth'])
        ->prefix('it-inventory')
        ->name('it-inventory.')
        ->controller(ItInventoryItemController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->middleware('permission:it-inventory.view')
                ->name('index');

            Route::get('/create', 'create')
                ->middleware('permission:it-inventory.create')
                ->name('create');

            Route::post('/', 'store')
                ->middleware('permission:it-inventory.create')
                ->name('store');

            Route::get('/{id}/edit', 'edit')
                ->middleware('permission:it-inventory.update')
                ->name('edit');

            Route::put('/{id}', 'update')
                ->middleware('permission:it-inventory.update')
                ->name('update');

            Route::delete('/it-inventory/{id}', 'destroy')
                ->middleware('permission:it-inventory.delete')
                ->name('destroy');

        });

    /*
    |--------------------------------------------------------------------------
    | CCTV (Job Orders)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth'])
        ->prefix('concern')
        ->name('concern.')
        ->controller(CctvController::class)
        ->group(function () {

            Route::get('/cctv', 'index')
                ->middleware('permission:cctv.view')
                ->name('cctv.index');

            Route::post('/cctv', 'store')
                ->middleware('permission:cctv.create')
                ->name('cctv.store');

            Route::get('/cctv/view/{id}', 'view')
                ->middleware('permission:cctv.view')
                ->name('cctv.view');

            Route::put('/cctv/{id}', 'update')
                ->middleware('permission:cctv.update')
                ->name('cctv.update');

            Route::delete('/cctv/{id}', 'destroy')
                ->middleware('permission:cctv.delete')
                ->name('cctv.destroy');

            Route::post('/cctv/{id}/accept', 'acceptTask')
                ->middleware('permission:cctv.update')
                ->name('cctv.accept');

            Route::post('/cctv/{id}/done', 'markAsDone')
                ->middleware('permission:cctv.update')
                ->name('cctv.done');

            Route::post('/cctv/{id}/note', 'addNote')
                ->middleware('permission:cctv.update')
                ->name('cctv.addnote');

            Route::post('/cctv/{id}/files', 'addFiles')
                ->middleware('permission:cctv.update')
                ->name('cctv.addfile');

            Route::get('/export/{type}', 'export')
                ->middleware('permission:cctv.export')
                ->name('export');

            Route::get('/cctv/bus-status', 'busStatus')
                ->middleware('permission:cctv.view')
                ->name('bus-status');

            Route::get('/cctv/bus-status/{bodyNumber}', 'busStatusShow')
                ->middleware('permission:cctv.view')
                ->name('bus-status.show');
        });

    Route::resource('cctv-parts', CctvController::class)
        ->middleware([
            'auth',
            'permission:cctv.view',
        ]);

    /*
    |--------------------------------------------------------------------------
    | HR Dashboard
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth'])
        ->prefix('hr')
        ->name('hr.')
        ->controller(HRDashboardController::class)
        ->group(function () {

            Route::get('/dashboard', 'index')
                ->middleware('permission:hr-dashboard.view')
                ->name('dashboard');

            Route::get('/dashboard/chart/employees-by-dept', 'employeesByDeptChart')
                ->middleware('permission:hr-dashboard.view')
                ->name('dashboard.chart.employees_by_dept');

            Route::get('/dashboard/chart/leaves-by-type', 'leavesByTypeChart')
                ->middleware('permission:hr-dashboard.view')
                ->name('dashboard.chart.leaves_by_type');

        });

    /*
    |--------------------------------------------------------------------------
    | Employees
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth'])
        ->prefix('employees')
        ->name('employees.')
        ->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Employee Records
            |--------------------------------------------------------------------------
            */
            Route::controller(EmployeeController::class)->group(function () {

                Route::get('/', 'index')
                    ->middleware('permission:employees.view')
                    ->name('staff.index');

                Route::post('/store', 'store')
                    ->middleware('permission:employees.create')
                    ->name('staff.store');

                Route::get('/employee/check-permanent-id', 'checkPermanentId')
                    ->middleware('permission:employees.view')
                    ->name('staff.checkPermanentId');

                Route::delete('/{id}', 'destroy')
                    ->middleware('permission:employees.delete')
                    ->name('staff.destroy');

                Route::get('/employee/{employee}', 'show')
                    ->middleware('permission:employees.view')
                    ->name('staff.show');

                Route::put('/employee/{employee}', 'update')
                    ->middleware('permission:employees.update')
                    ->name('update');

                Route::post('/employee/{employee}/201', 'updateAssets')
                    ->middleware('permission:employees.update')
                    ->name('assets.update');

                Route::post('/{employee}/history', 'storeHistory')
                    ->middleware('permission:employees.update')
                    ->name('staff.history.store');

                Route::delete('/{employee}/history/{history}', 'destroyHistory')
                    ->middleware('permission:employees.update')
                    ->name('staff.history.destroy');

                Route::post('/{employee}/attachments', 'storeAttachment')
                    ->middleware('permission:employees.update')
                    ->name('staff.attachments.store');

                Route::delete('/{employee}/attachments/{attachment}', 'destroyAttachment')
                    ->middleware('permission:employees.update')
                    ->name('staff.attachments.destroy');

                Route::put('/employees/{employee}/status-details', 'updateStatusDetails')
                    ->middleware('permission:employees.update')
                    ->name('status-details.update');

                Route::get('/{employee}/print', 'print201')
                    ->middleware('permission:employees.view')
                    ->name('staff.print');

                Route::get('/departments/{id}/positions', 'getPositions')
                    ->middleware('permission:employees.view')
                    ->name('positions');

                Route::get('/{employee}/history/{history}/edit', 'editHistory')
                    ->middleware('permission:employees.update')
                    ->name('staff.history.edit');

                Route::put('/{employee}/history/{history}', 'updateHistory')
                    ->middleware('permission:employees.update')
                    ->name('staff.history.update');
            });

            /*
            |--------------------------------------------------------------------------
            | Departments
            |--------------------------------------------------------------------------
            */
            Route::controller(DepartmentController::class)->group(function () {

                Route::get('/departments', 'index')
                    ->middleware('permission:departments.view')
                    ->name('departments.index');

                Route::post('/departments', 'store')
                    ->middleware('permission:departments.create')
                    ->name('departments.store');

                Route::post('/departments/position', 'storePosition')
                    ->middleware('permission:departments.create')
                    ->name('departments.position.store');

                Route::delete('/departments/{department}', 'destroy')
                    ->middleware('permission:departments.delete')
                    ->name('departments.destroy');

                Route::delete('/positions/{position}', 'destroyPosition')
                    ->middleware('permission:departments.delete')
                    ->name('positions.destroy');

                Route::get('/departments/{id}/positions', 'positions')
                    ->middleware('permission:departments.view')
                    ->name('departments.positions');
            });

        });

    /*
    |--------------------------------------------------------------------------
    | Policy Management
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth'])
        ->prefix('violation')
        ->name('violation.')
        ->group(function () {

            Route::controller(HrOffenseController::class)->group(function () {

                Route::get('/offenses', 'index')
                    ->middleware('permission:violations.view')
                    ->name('offenses.index');

                Route::post('/offenses/store', 'store')
                    ->middleware('permission:violations.create')
                    ->name('offenses.store');

            });

        });

    /*
    |--------------------------------------------------------------------------
    | Leave Management
    |--------------------------------------------------------------------------
    */
    /*
    |--------------------------------------------------------------------------
    | Driver Leave
    |--------------------------------------------------------------------------
    */
    Route::prefix('driver-leave')
        ->middleware(['auth'])
        ->name('driver-leave.')
        ->controller(DriverLeaveController::class)
        ->group(function () {

            Route::get('driver', 'index')
                ->middleware('permission:driver-leave.view')
                ->name('driver.index');

            Route::get('driver/create', 'create')
                ->middleware('permission:driver-leave.create')
                ->name('driver.create');

            Route::post('driver/store', 'store')
                ->middleware('permission:driver-leave.create')
                ->name('driver.store');

            Route::get('driver/{leave}/edit', 'edit')
                ->middleware('permission:driver-leave.update')
                ->name('driver.edit');

            Route::put('driver/{leave}', 'update')
                ->middleware('permission:driver-leave.update')
                ->name('driver.update');

            Route::post('{leave}/action', 'action')
                ->middleware('permission:driver-leave.update')
                ->name('driver.action');
        });

    /*
    |--------------------------------------------------------------------------
    | Conductor Leave
    |--------------------------------------------------------------------------
    */
    Route::prefix('conductor-leave')
        ->middleware(['auth'])
        ->name('conductor-leave.')
        ->controller(ConductorLeaveController::class)
        ->group(function () {

            Route::get('conductor', 'index')
                ->middleware('permission:conductor-leave.view')
                ->name('conductor.index');

            Route::get('conductor/create', 'create')
                ->middleware('permission:conductor-leave.create')
                ->name('conductor.create');

            Route::post('conductor/store', 'store')
                ->middleware('permission:conductor-leave.create')
                ->name('conductor.store');

            Route::get('conductor/{leave}/edit', 'edit')
                ->middleware('permission:conductor-leave.update')
                ->name('conductor.edit');

            Route::put('conductor/{leave}', 'update')
                ->middleware('permission:conductor-leave.update')
                ->name('conductor.update');

            Route::post('{leave}/action', 'action')
                ->middleware('permission:conductor-leave.update')
                ->name('conductor.action');
        });

    /*
    |--------------------------------------------------------------------------
    | Employee Leave
    |--------------------------------------------------------------------------
    */
    Route::prefix('employee-leave')
        ->middleware(['auth'])
        ->name('employee-leave.')
        ->controller(EmployeeLeaveController::class)
        ->group(function () {

            Route::get('employee', 'index')
                ->middleware('permission:employee-leave.view')
                ->name('employee.index');

            Route::get('employee/create', 'create')
                ->middleware('permission:employee-leave.create')
                ->name('employee.create');

            Route::post('employee/store', 'store')
                ->middleware('permission:employee-leave.create')
                ->name('employee.store');

            Route::get('employee/{leave}/edit', 'edit')
                ->middleware('permission:employee-leave.update')
                ->name('employee.edit');

            Route::put('employee/{leave}', 'update')
                ->middleware('permission:employee-leave.update')
                ->name('employee.update');

            Route::post('{leave}/action', 'action')
                ->middleware('permission:employee-leave.update')
                ->name('employee.action');
        });

    /*
    /*
|--------------------------------------------------------------------------
| Holiday Management
|--------------------------------------------------------------------------
*/

    Route::middleware(['auth'])->group(function () {

        Route::resource('holidays', HolidayController::class)
            ->middleware([
                'index' => 'permission:holidays.view',
                'create' => 'permission:holidays.create',
                'store' => 'permission:holidays.create',
                'edit' => 'permission:holidays.update',
                'update' => 'permission:holidays.update',
                'destroy' => 'permission:holidays.delete',
                'show' => 'permission:holidays.view',
            ]);
    });

    /*
    |--------------------------------------------------------------------------
    | Plotting Schedule
    |--------------------------------------------------------------------------
    */

    Route::prefix('payroll-plotting')
        ->middleware(['auth'])
        ->name('payroll-plotting.')
        ->controller(EmployeePlottingScheduleController::class)
        ->group(function () {

            // Display permanent plotting schedule
            Route::get('/', 'index')
                ->middleware('permission:payroll-plotting.view')
                ->name('index');

            // Save the permanent schedule
            Route::post('/save', 'save')
                ->middleware('permission:payroll-plotting.update')
                ->name('save');

            // Optional: quick-fill (apply default shifts to the table)
            Route::post('/quick-fill', 'quickFill')
                ->middleware('permission:payroll-plotting.update')
                ->name('quick-fill');

            // Optional: employee search suggestions
            Route::get('/search-suggestions', 'searchSuggestions')
                ->middleware('permission:payroll-plotting.view')
                ->name('search-suggestions');
        });

    /*
    |--------------------------------------------------------------------------
    | Manual Biometrics Encoding
    |--------------------------------------------------------------------------
    */

    Route::prefix('manual-biometrics')
        ->middleware(['auth'])
        ->name('manual-biometrics.')
        ->controller(ManualBiometricsEncodingController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->middleware('permission:manual-biometrics.view')
                ->name('index');

            Route::get('/search-employees', 'searchEmployees')
                ->middleware('permission:manual-biometrics.view')
                ->name('search-employees');

            Route::post('/', 'store')
                ->middleware('permission:manual-biometrics.create')
                ->name('store');
        });

    /*
    |--------------------------------------------------------------------------
    | Payroll Attendance Adjustments
    |--------------------------------------------------------------------------
    */

    Route::prefix('payroll-attendance-adjustments')
        ->middleware(['auth'])
        ->name('payroll-attendance-adjustments.')
        ->controller(PayrollAttendanceAdjustmentController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->middleware('permission:payroll-attendance-adjustments.view')
                ->name('index');

            Route::get('/create', 'create')
                ->middleware('permission:payroll-attendance-adjustments.create')
                ->name('create');

            Route::post('/', 'store')
                ->middleware('permission:payroll-attendance-adjustments.create')
                ->name('store');

            Route::get('/{payrollAttendanceAdjustment}/edit', 'edit')
                ->middleware('permission:payroll-attendance-adjustments.update')
                ->name('edit');

            Route::put('/{payrollAttendanceAdjustment}', 'update')
                ->middleware('permission:payroll-attendance-adjustments.update')
                ->name('update');

            Route::delete('/{payrollAttendanceAdjustment}', 'destroy')
                ->middleware('permission:payroll-attendance-adjustments.delete')
                ->name('destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Attendance Summary
    |--------------------------------------------------------------------------
    */

    Route::prefix('attendance-summary')
        ->middleware(['auth'])
        ->name('attendance-summary.')
        ->controller(AttendanceSummaryController::class)
        ->group(function () {
            Route::get('/', 'index')
                ->middleware('permission:attendance-summary.view')
                ->name('index');

            Route::post('/create', 'rebuild')
                ->middleware('permission:attendance-summary.create')
                ->name('rebuild');

            Route::get('/attendance-summary/export-payroll', 'exportPayroll')
                ->middleware('permission:attendance-summary.export')
                ->name('export-payroll');
        });

    /*
    |--------------------------------------------------------------------------
    | Employee Salaries
    |--------------------------------------------------------------------------
    */

    Route::prefix('payroll')
        ->middleware(['auth'])
        ->group(function () {
            Route::get(
                'employee-salaries/sync',
                [PayrollEmployeeSalaryController::class, 'syncFromBiometrics']
            )
                ->middleware('permission:employee-salaries.update')
                ->name('payroll-employee-salaries.sync');

            Route::resource(
                'employee-salaries',
                PayrollEmployeeSalaryController::class
            )
                ->except(['show'])
                ->parameters([
                    'employee-salaries' => 'payrollEmployeeSalary',
                ])
                ->names('payroll-employee-salaries')
                ->middlewareFor(['index'], 'permission:employee-salaries.view')
                ->middlewareFor(['create', 'store'], 'permission:employee-salaries.create')
                ->middlewareFor(['edit', 'update'], 'permission:employee-salaries.update')
                ->middlewareFor(['destroy'], 'permission:employee-salaries.delete');
        });

    /*
    |--------------------------------------------------------------------------
    | Payroll
    |--------------------------------------------------------------------------
    */

    Route::prefix('payroll')
        ->middleware(['auth'])
        ->name('payroll.')
        ->controller(PayrollController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->middleware('permission:payroll.view')
                ->name('index');

            Route::get('/create', 'create')
                ->middleware('permission:payroll.create')
                ->name('create');

            Route::post('/', 'store')
                ->middleware('permission:payroll.create')
                ->name('store');

            Route::get('/payrolls/{payroll}/items/{item}', 'showItem')
                ->middleware('permission:payroll.view')
                ->whereNumber('payroll')
                ->name('items.show');

            Route::post('/payrolls/{payroll}/finalize', 'finalize')
                ->middleware('permission:payroll.finalize')
                ->whereNumber('payroll')
                ->name('finalize');

            Route::get('/payrolls/{payroll}/export/excel', 'exportExcel')
                ->middleware('permission:payroll.export')
                ->whereNumber('payroll')
                ->name('export.excel');

            Route::get('/payrolls/{payroll}/export/pdf', 'exportPdf')
                ->middleware('permission:payroll.export')
                ->whereNumber('payroll')
                ->name('export.pdf');

            Route::get('/{payroll}', 'show')
                ->middleware('permission:payroll.view')
                ->whereNumber('payroll')
                ->name('show');

            Route::delete('/{payroll}', 'destroy')
                ->middleware('permission:payroll.delete')
                ->whereNumber('payroll')
                ->name('destroy');
        });

    /*
    /*
|--------------------------------------------------------------------------
| Claims Management
|--------------------------------------------------------------------------
*/

    Route::middleware(['auth'])
        ->prefix('claims')
        ->name('claims.')
        ->controller(ClaimController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->middleware('permission:claims.view')
                ->name('index');

            Route::post('/store', 'store')
                ->middleware('permission:claims.create')
                ->name('store');

            Route::put('/{claim}', 'update')
                ->middleware('permission:claims.update')
                ->name('update');

            Route::delete('/{claim}', 'destroy')
                ->middleware('permission:claims.delete')
                ->name('destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Mirasol Biometrics Management
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth'])
        ->prefix('mirasol-logs')
        ->name('mirasol-logs.')
        ->controller(MirasolBiometricsLogController::class)
        ->group(function () {

            Route::get('/', 'index')
                ->middleware('permission:mirasol-logs.view')
                ->name('index');

            Route::post('/sync-start', 'startSync')
                ->middleware('permission:mirasol-logs.sync')
                ->name('sync-start');

            Route::get('/sync-status', 'syncStatus')
                ->middleware('permission:mirasol-logs.view')
                ->name('sync-status');
        });

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth'])->group(function () {

        Route::prefix('authentication')
            ->name('authentication.')
            ->controller(UserManagementController::class)
            ->group(function () {

                Route::get('/users', 'index')
                    ->middleware('permission:users.view')
                    ->name('users.index');

                Route::get('/users/create', 'create')
                    ->middleware('permission:users.create')
                    ->name('users.create');

                Route::post('/users/store', 'store')
                    ->middleware('permission:users.create')
                    ->name('users.store');

                Route::get('/users/edit/{id}', 'edit')
                    ->middleware('permission:users.update')
                    ->name('users.edit');

                Route::post('/users/update/{id}', 'update')
                    ->middleware('permission:users.update')
                    ->name('users.update');

                Route::post('/users/reset-password/{id}', 'resetPassword')
                    ->middleware('permission:users.update')
                    ->name('users.reset.password');

                Route::get('/users/status/{id}', 'status')
                    ->middleware('permission:users.update')
                    ->name('users.status');
            });

        Route::prefix('roles')
            ->name('roles.')
            ->controller(RoleController::class)
            ->group(function () {
                Route::get('/', 'index')
                    ->middleware('permission:roles.view')
                    ->name('index');

                Route::post('/store', 'store')
                    ->middleware('permission:roles.create')
                    ->name('store');

                Route::post('/sync-permissions', 'syncPermissions')
                    ->middleware('permission:roles.update')
                    ->name('sync-permissions');

                Route::put('/update/{role}', 'update')
                    ->middleware('permission:roles.update')
                    ->name('update');

                Route::delete('/destroy/{role}', 'destroy')
                    ->middleware('permission:roles.delete')
                    ->name('destroy');
            });

    });

    /*
    |--------------------------------------------------------------------------
    | Maintenance
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth'])->group(function () {

        Route::resource('allbus', AllBusController::class)
            ->parameters(['allbus' => 'bus']);

        Route::prefix('request')
            ->name('request.')
            ->controller(RequestController::class)
            ->group(function () {

                Route::get('/index', 'index')
                    ->middleware('permission:request.view')
                    ->name('index');

                Route::get('/create', 'create')
                    ->middleware('permission:request.create')
                    ->name('create');

                Route::post('/store', 'store')
                    ->middleware('permission:request.create')
                    ->name('store');

                Route::get('/edit/{id}', 'edit')
                    ->middleware('permission:request.update')
                    ->name('edit');

                Route::put('/update/{id}', 'update')
                    ->middleware('permission:request.update')
                    ->name('update');

                Route::get('/status/{id}', 'destroy')
                    ->middleware('permission:request.delete')
                    ->name('destroy');
            });

        Route::prefix('category')
            ->name('category.')
            ->controller(CategoryController::class)
            ->group(function () {

                Route::get('/index', 'index')
                    ->middleware('permission:category.view')
                    ->name('index');

                Route::post('/store', 'store')
                    ->middleware('permission:category.create')
                    ->name('store');

                Route::get('/edit/{id}', 'edit')
                    ->middleware('permission:category.update')
                    ->name('edit');

                Route::post('/update/{id}', 'update')
                    ->middleware('permission:category.update')
                    ->name('update');

                Route::get('/status/{id}', 'destroy')
                    ->middleware('permission:category.delete')
                    ->name('destroy');
            });

        Route::prefix('items')
            ->name('items.')
            ->controller(ItemsController::class)
            ->group(function () {

                Route::get('/index', 'index')
                    ->middleware('permission:items.view')
                    ->name('index');

                Route::post('/store', 'store')
                    ->middleware('permission:items.create')
                    ->name('store');

                Route::get('/edit/{id}', 'edit')
                    ->middleware('permission:items.update')
                    ->name('edit');

                Route::post('/update/{id}', 'update')
                    ->middleware('permission:items.update')
                    ->name('update');

                Route::delete('/destroy/{id}', 'destroy')
                    ->middleware('permission:items.delete')
                    ->name('destroy');

                Route::get('/dashboard', 'dashboard')
                    ->middleware('permission:items.view')
                    ->name('dashboard');
            });

        Route::prefix('parts-out')
            ->name('parts-out.')
            ->controller(PartsOutController::class)
            ->group(function () {
                Route::get('/', 'index')
                    ->middleware('permission:parts-out.view')
                    ->name('index');

                Route::get('/create', 'create')
                    ->middleware('permission:parts-out.create')
                    ->name('create');

                Route::get('/search-products', 'searchProducts')
                    ->middleware('permission:parts-out.view')
                    ->name('search-products');

                Route::post('/', 'store')
                    ->middleware('permission:parts-out.create')
                    ->name('store');

                Route::get('/{partsOut}', 'show')
                    ->middleware('permission:parts-out.view')
                    ->name('show');

                Route::get('/{partsOut}/edit', 'edit')
                    ->middleware('permission:parts-out.update')
                    ->name('edit');

                Route::put('/{partsOut}', 'update')
                    ->middleware('permission:parts-out.update')
                    ->name('update');

                Route::patch('/{partsOut}/cancel', 'cancel')
                    ->middleware('permission:parts-out.cancel')
                    ->name('cancel');

                Route::get('/{partsOut}/print', 'print')
                    ->middleware('permission:parts-out.view')
                    ->name('print');

                Route::post('/{partsOut}/rollback', 'rollback')
                    ->middleware('permission:parts-out.rollback')
                    ->name('rollback');
            });

        Route::prefix('receivings')
            ->name('receivings.')
            ->controller(ReceivingController::class)
            ->group(function () {
                Route::get('/', 'index')
                    ->middleware('permission:receivings.view')
                    ->name('index');

                Route::get('/create', 'create')
                    ->middleware('permission:receivings.create')
                    ->name('create');

                Route::get('/search-products', 'searchProducts')
                    ->middleware('permission:receivings.view')
                    ->name('search-products');

                Route::post('/store', 'store')
                    ->middleware('permission:receivings.create')
                    ->name('store');

                Route::get('/{id}', 'show')
                    ->middleware('permission:receivings.view')
                    ->name('show');

                Route::post('/{receiving}/items/{item}/rollback', 'rollbackItem')
                    ->middleware('permission:receivings.rollback')
                    ->name('rollback');
            });

        Route::prefix('received')
            ->name('received.')
            ->controller(PurchaseReceiveController::class)
            ->group(function () {
                Route::get('po/receiving', 'index')
                    ->middleware('permission:received.view')
                    ->name('index');

                Route::get('po/receiving/{id}', 'details')
                    ->middleware('permission:received.view')
                    ->name('details');

                Route::post('po/item/{id}/receive', 'receive')
                    ->middleware('permission:received.receive')
                    ->name('received');
            });

        Route::prefix('buses')
            ->name('buses.')
            ->controller(BusDetailController::class)
            ->group(function () {

                Route::get('/', 'index')
                    ->middleware('permission:buses.view')
                    ->name('index');

                Route::get('/{busDetail}', 'show')
                    ->middleware('permission:buses.view')
                    ->name('show');

                Route::get('/{busDetail}/maintenance-history', 'maintenanceHistory')
                    ->middleware('permission:buses.view')
                    ->name('maintenance-history');
            });

    });

    /*
    |--------------------------------------------------------------------------
    | Stock Transfers
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth'])->group(function () {
        Route::get(
            'stock-transfers/search-products',
            [StockTransferController::class, 'searchProducts']
        )
            ->middleware('permission:stock-transfers.view')
            ->name('stock-transfers.search-products');

        Route::resource('stock-transfers', StockTransferController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->middlewareFor(['index', 'show'], 'permission:stock-transfers.view')
            ->middlewareFor(['create', 'store'], 'permission:stock-transfers.create');

        Route::post('/stock-transfers/{stock_transfer}/rollback', [StockTransferController::class, 'rollback'])
            ->middleware('permission:stock-transfers.rollback')
            ->name('stock-transfers.rollback');
    });

    /*
    |--------------------------------------------------------------------------
    | Odometer Reports
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth'])
        ->prefix('odometer')
        ->name('odometer.')
        ->controller(OdometerReportController::class)
        ->group(function () {

            Route::get('/index', 'index')
                ->middleware('permission:odometer.view')
                ->name('index');

            Route::post('/maintenance/diesel-stock', 'storeDieselStock')
                ->middleware('permission:odometer.update')
                ->name('diesel-stock.store');

            Route::post('/maintenance/odometer/manual', 'storeManualOdometer')
                ->middleware('permission:odometer.create')
                ->name('manual.store');

            Route::delete('/maintenance/odometer/{odometerSubmission}', 'destroyOdometer')
                ->middleware('permission:odometer.delete')
                ->name('destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Accounting - Purchase Orders
    |--------------------------------------------------------------------------
    */

    Route::model('order', App\Models\PurchaseOrder::class);

    Route::middleware(['auth'])
        ->prefix('purchase')
        ->name('purchase.')
        ->controller(AccountingController::class)
        ->group(function () {

            Route::get('/index', 'index')
                ->middleware('permission:purchase.view')
                ->name('index');

            Route::post('/update/{order}', 'update')
                ->middleware('permission:purchase.update')
                ->name('update');
        });

});
