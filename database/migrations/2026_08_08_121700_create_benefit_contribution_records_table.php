<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benefit_contribution_records', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('payroll_id')
                ->constrained('payrolls')
                ->restrictOnDelete();

            $table->foreignId('payroll_item_id')
                ->unique()
                ->constrained('payroll_items')
                ->restrictOnDelete();

            $table->foreignId('employee_biometric_id')
                ->nullable()
                ->constrained('employee_biometrics')
                ->nullOnDelete();

            $table->foreignId('employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->foreignId('payroll_employee_salary_id')
                ->nullable()
                ->constrained('payroll_employee_salaries')
                ->nullOnDelete();

            $table->foreignId('posted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->unsignedTinyInteger('garage_group')->nullable();
            $table->unsignedTinyInteger('contribution_month');
            $table->unsignedSmallInteger('contribution_year');

            $table->date('period_start');
            $table->date('period_end');
            $table->string('payroll_number');

            $table->string('employee_no')->nullable();
            $table->string('employee_name');
            $table->string('company_name')->nullable();

            $table->string('sss_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('pagibig_number')->nullable();

            // Compensation snapshots used by the finalized payroll.
            $table->decimal('gross_compensation', 15, 2)->default(0);
            $table->decimal('monthly_basic_salary', 15, 2)->default(0);

            // SSS Circular No. 2024-006 breakdown.
            $table->decimal('sss_compensation_basis', 15, 2)->default(0);
            $table->decimal('sss_compensation_range_minimum', 15, 2)->default(0);
            $table->decimal('sss_compensation_range_maximum', 15, 2)->nullable();
            $table->decimal('sss_msc', 15, 2)->default(0);
            $table->decimal('sss_regular_ss_msc', 15, 2)->default(0);
            $table->decimal('sss_mpf_msc', 15, 2)->default(0);

            $table->decimal('sss_employee_regular_ss', 15, 2)->default(0);
            $table->decimal('sss_employee_mpf', 15, 2)->default(0);
            $table->decimal('sss_employee_total', 15, 2)->default(0);

            $table->decimal('sss_employer_regular_ss', 15, 2)->default(0);
            $table->decimal('sss_employer_mpf', 15, 2)->default(0);
            $table->decimal('sss_employer_ec', 15, 2)->default(0);
            $table->decimal('sss_employer_total', 15, 2)->default(0);
            $table->decimal('sss_total_contribution', 15, 2)->default(0);

            // PhilHealth direct contributor contribution.
            $table->decimal('philhealth_basis', 15, 2)->default(0);
            $table->decimal('philhealth_salary_base', 15, 2)->default(0);
            $table->decimal('philhealth_premium_rate', 8, 6)->default(0.05);
            $table->decimal('philhealth_employee', 15, 2)->default(0);
            $table->decimal('philhealth_employer', 15, 2)->default(0);
            $table->decimal('philhealth_total', 15, 2)->default(0);

            // Pag-IBIG / HDMF Circular No. 460 contribution.
            $table->decimal('pagibig_basis', 15, 2)->default(0);
            $table->decimal('pagibig_fund_salary', 15, 2)->default(0);
            $table->decimal('pagibig_employee_rate', 8, 6)->default(0);
            $table->decimal('pagibig_employer_rate', 8, 6)->default(0.02);
            $table->decimal('pagibig_employee', 15, 2)->default(0);
            $table->decimal('pagibig_employer', 15, 2)->default(0);
            $table->decimal('pagibig_total', 15, 2)->default(0);

            // Overall finalized statutory contribution totals.
            $table->decimal('employee_total', 15, 2)->default(0);
            $table->decimal('employer_total', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            $table->timestamp('posted_at');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(
                ['contribution_year', 'contribution_month', 'garage_group'],
                'benefit_records_period_group_idx'
            );

            $table->index(
                ['employee_biometric_id', 'contribution_year', 'contribution_month'],
                'benefit_records_employee_period_idx'
            );

            $table->index('company_name', 'benefit_records_company_idx');
            $table->index('posted_at', 'benefit_records_posted_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benefit_contribution_records');
    }
};
