<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payrolls')) {
            Schema::create('payrolls', function (Blueprint $table) {
                $table->id();
                $table->string('payroll_number')->unique();
                $table->unsignedTinyInteger('cutoff_month');
                $table->unsignedSmallInteger('cutoff_year');
                $table->string('cutoff_type', 20);
                $table->unsignedTinyInteger('contribution_month')->nullable();
                $table->unsignedSmallInteger('contribution_year')->nullable();
                $table->date('period_start');
                $table->date('period_end');
                $table->text('remarks')->nullable();
                $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('generated_at')->nullable();
                $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('finalized_at')->nullable();
                $table->string('status', 30)->default('draft');
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['cutoff_year', 'cutoff_month', 'cutoff_type']);
                $table->index(['contribution_year', 'contribution_month']);
            });
        } else {
            Schema::table('payrolls', function (Blueprint $table) {
                $this->addColumnIfMissing($table, 'contribution_month', fn () => $table->unsignedTinyInteger('contribution_month')->nullable()->after('cutoff_type'));
                $this->addColumnIfMissing($table, 'contribution_year', fn () => $table->unsignedSmallInteger('contribution_year')->nullable()->after('contribution_month'));
                $this->addColumnIfMissing($table, 'meta', fn () => $table->json('meta')->nullable()->after('status'));
            });
        }

        if (! Schema::hasTable('payroll_items')) {
            Schema::create('payroll_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnDelete();
                $table->unsignedBigInteger('employee_id')->nullable();
                $table->unsignedBigInteger('payroll_employee_salary_id')->nullable();
                $table->string('biometric_employee_id')->nullable();
                $table->string('employee_no')->nullable();
                $table->string('employee_name');
                $table->string('crosschex_id')->nullable();
                $table->string('rate_type', 30)->nullable();
                $table->decimal('monthly_rate', 15, 2)->default(0);
                $table->decimal('daily_rate', 15, 6)->default(0);
                $table->decimal('hourly_rate', 15, 6)->default(0);
                $table->decimal('minute_rate', 15, 6)->default(0);
                $table->decimal('total_scheduled_days', 10, 2)->default(0);
                $table->decimal('total_worked_days', 10, 2)->default(0);
                $table->decimal('total_payable_days', 10, 2)->default(0);
                $table->decimal('total_payable_hours', 10, 2)->default(0);
                $table->integer('total_worked_minutes')->default(0);
                $table->integer('total_late_minutes')->default(0);
                $table->integer('total_undertime_minutes')->default(0);
                $table->integer('total_overtime_minutes')->default(0);
                $table->decimal('total_absent_days', 10, 2)->default(0);
                $table->integer('total_rest_day_worked')->default(0);
                $table->integer('total_holiday_worked')->default(0);
                $table->integer('total_leave_days')->default(0);
                $table->decimal('regular_pay', 15, 2)->default(0);
                $table->decimal('gross_pay', 15, 2)->default(0);
                $table->decimal('late_deduction', 15, 2)->default(0);
                $table->decimal('undertime_deduction', 15, 2)->default(0);
                $table->decimal('absence_deduction', 15, 2)->default(0);
                $table->decimal('overtime_pay', 15, 2)->default(0);
                $table->decimal('holiday_pay', 15, 2)->default(0);
                $table->decimal('rest_day_pay', 15, 2)->default(0);
                $table->decimal('leave_pay', 15, 2)->default(0);
                $table->decimal('taxable_compensation', 15, 2)->default(0);
                $table->decimal('sss_employee', 15, 2)->default(0);
                $table->decimal('sss_employer', 15, 2)->default(0);
                $table->decimal('philhealth_employee', 15, 2)->default(0);
                $table->decimal('philhealth_employer', 15, 2)->default(0);
                $table->decimal('pagibig_employee', 15, 2)->default(0);
                $table->decimal('pagibig_employer', 15, 2)->default(0);
                $table->decimal('withholding_tax', 15, 2)->default(0);
                $table->decimal('total_employee_government_deductions', 15, 2)->default(0);
                $table->decimal('total_employer_government_contributions', 15, 2)->default(0);
                $table->decimal('other_additions', 15, 2)->default(0);
                $table->decimal('other_deductions', 15, 2)->default(0);
                $table->decimal('net_pay', 15, 2)->default(0);
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['payroll_id', 'employee_no']);
                $table->index(['payroll_id', 'biometric_employee_id']);
            });
        } else {
            Schema::table('payroll_items', function (Blueprint $table) {
                $this->addColumnIfMissing($table, 'payroll_employee_salary_id', fn () => $table->unsignedBigInteger('payroll_employee_salary_id')->nullable()->after('employee_id'));
                $this->addColumnIfMissing($table, 'crosschex_id', fn () => $table->string('crosschex_id')->nullable()->after('employee_name'));
                $this->addColumnIfMissing($table, 'rate_type', fn () => $table->string('rate_type', 30)->nullable()->after('crosschex_id'));
                $this->addColumnIfMissing($table, 'total_scheduled_days', fn () => $table->decimal('total_scheduled_days', 10, 2)->default(0)->after('minute_rate'));
                $this->addColumnIfMissing($table, 'regular_pay', fn () => $table->decimal('regular_pay', 15, 2)->default(0)->after('total_leave_days'));
                $this->addColumnIfMissing($table, 'withholding_tax', fn () => $table->decimal('withholding_tax', 15, 2)->default(0)->after('pagibig_employer'));
                $this->addColumnIfMissing($table, 'meta', fn () => $table->json('meta')->nullable()->after('net_pay'));
            });
        }
    }

    public function down(): void
    {
        // Safe enhancement migration: intentionally do not drop existing payroll data.
    }

    private function addColumnIfMissing(Blueprint $table, string $column, callable $callback): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $callback();
        }
    }
};
