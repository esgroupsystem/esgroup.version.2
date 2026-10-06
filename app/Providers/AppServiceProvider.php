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
use App\Models\PayrollRule;
use App\Models\PayrollSettingVersion;
use App\Models\User;
use App\Observers\PayrollAuditObserver;
use App\Services\Payroll\PayrollSettingsService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Throwable;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One instance per request, so the settings applied for a payroll run are the ones it reports.
        $this->app->singleton(PayrollSettingsService::class);
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

        // Developers always hold every permission, including permissions added
        // after their role was last synced. Returning null (not false) for
        // everyone else keeps normal role/permission checks in charge.
        Gate::before(static fn ($user): ?bool => $user instanceof User && $user->isDeveloper() ? true : null);

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
            PayrollSettingVersion::class,
            PayrollRule::class,
        ] as $auditedModel) {
            $auditedModel::observe(PayrollAuditObserver::class);
        }

        // Payroll Settings: rates in effect today replace the config file values. A payroll
        // run re-applies the version of its own period (PayrollSettingsService::using()).
        $this->app->booted(function (): void {
            try {
                $this->app->make(PayrollSettingsService::class)->apply();
            } catch (Throwable) {
                // Table not migrated yet (fresh install, early migrations): keep the config files.
            }
        });

        Blade::if('role', function (...$roles) {
            return Auth::check() && Auth::user()->hasAnyRole($roles);
        });
    }
}
