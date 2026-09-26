<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Employee Rates: is the weekly day off paid?
 *   Paid (default)  - an unworked day off is paid 1 day (daily-rate employees,
 *                     when the cutoff has the minimum valid log days); monthly
 *                     employees keep the fixed half-month salary.
 *   Not paid        - pay only for days actually worked (a worked 31st counts);
 *                     monthly employees are paid daily rate x days worked.
 *
 * Existing daily-rate employees were never paid for day offs, so they start
 * as Not paid and nobody's pay changes until HR switches them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_employee_salaries', function (Blueprint $table): void {
            $table->boolean('paid_day_off')->default(true)->after('paid_night_differential');
        });

        DB::table('payroll_employee_salaries')->where('rate_type', 'daily')->update(['paid_day_off' => false]);
    }

    public function down(): void
    {
        Schema::table('payroll_employee_salaries', function (Blueprint $table): void {
            $table->dropColumn('paid_day_off');
        });
    }
};
