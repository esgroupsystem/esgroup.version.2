<?php

declare(strict_types=1);

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
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
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
        if (! app()->isProduction()) {
            Model::preventLazyLoading();
            Model::preventSilentlyDiscardingAttributes();
        }

        Schema::defaultStringLength(191);

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)
                ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()));
        });

        RateLimiter::for('expensive', function (Request $request) {
            return [
                Limit::perMinute(10)->by('user:'.($request->user()?->getAuthIdentifier() ?? $request->ip())),
                Limit::perMinute(30)->by('ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('api-login', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email', '')));

            return [
                Limit::perMinute(20)->by('ip:'.$request->ip()),
                Limit::perMinute(5)->by('email:'.sha1($email)),
            ];
        });

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
            return Auth::check() && Auth::user()->hasAnyRole($roles);
        });
    }
}
