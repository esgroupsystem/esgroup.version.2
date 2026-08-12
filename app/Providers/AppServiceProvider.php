<?php

namespace App\Providers;

use App\Models\BenefitContributionRecord;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\Holiday;
use App\Models\MirasolBiometricsLog;
use App\Models\PaymentLog;
use App\Models\Payroll;
use App\Models\PayrollAttendanceAdjustment;
use App\Models\PayrollBenefitSettlement;
use App\Models\PayrollEmployeeSalary;
use App\Models\PayrollEmployeeSalaryOtherDeduction;
use App\Models\PayrollItem;
use App\Models\PayrollReportLog;
use App\Observers\PayrollAuditObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        foreach ([
            Payroll::class,
            PayrollItem::class,
            PayrollAttendanceAdjustment::class,
            BenefitContributionRecord::class,
            EmployeeBiometric::class,
            PayrollEmployeeSalary::class,
            PayrollEmployeeSalaryOtherDeduction::class,
            EmployeePlottingSchedule::class,
            PayrollBenefitSettlement::class,
            PaymentLog::class,
            PayrollReportLog::class,
            Holiday::class,
            MirasolBiometricsLog::class,
        ] as $auditedModel) {
            $auditedModel::observe(PayrollAuditObserver::class);
        }

        Blade::if('role', function (...$roles) {
            return Auth::check() && in_array(Auth::user()->role, $roles, true);
        });
    }
}
