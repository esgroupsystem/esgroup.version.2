<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payroll_employee_salaries')) {
            return;
        }

        Schema::table('payroll_employee_salaries', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_employee_salaries', 'paid_night_differential')) {
                $table->boolean('paid_night_differential')
                    ->default(false)
                    ->after('sim_load_release_schedule');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('payroll_employee_salaries')) {
            return;
        }

        Schema::table('payroll_employee_salaries', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_employee_salaries', 'paid_night_differential')) {
                $table->dropColumn('paid_night_differential');
            }
        });
    }
};
