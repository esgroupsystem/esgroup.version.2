<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (! Schema::hasColumn('payrolls', 'finalized_by')) {
                $table->unsignedBigInteger('finalized_by')->nullable()->after('generated_by');
            }
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_items', 'taxable_compensation')) {
                $table->decimal('taxable_compensation', 12, 2)->default(0)->after('leave_pay');
            }

            if (! Schema::hasColumn('payroll_items', 'sss_employee')) {
                $table->decimal('sss_employee', 12, 2)->default(0)->after('taxable_compensation');
            }

            if (! Schema::hasColumn('payroll_items', 'sss_employer')) {
                $table->decimal('sss_employer', 12, 2)->default(0)->after('sss_employee');
            }

            if (! Schema::hasColumn('payroll_items', 'philhealth_employee')) {
                $table->decimal('philhealth_employee', 12, 2)->default(0)->after('sss_employer');
            }

            if (! Schema::hasColumn('payroll_items', 'philhealth_employer')) {
                $table->decimal('philhealth_employer', 12, 2)->default(0)->after('philhealth_employee');
            }

            if (! Schema::hasColumn('payroll_items', 'pagibig_employee')) {
                $table->decimal('pagibig_employee', 12, 2)->default(0)->after('philhealth_employer');
            }

            if (! Schema::hasColumn('payroll_items', 'pagibig_employer')) {
                $table->decimal('pagibig_employer', 12, 2)->default(0)->after('pagibig_employee');
            }

            if (! Schema::hasColumn('payroll_items', 'withholding_tax')) {
                $table->decimal('withholding_tax', 12, 2)->default(0)->after('pagibig_employer');
            }

            if (! Schema::hasColumn('payroll_items', 'total_employee_government_deductions')) {
                $table->decimal('total_employee_government_deductions', 12, 2)->default(0)->after('withholding_tax');
            }

            if (! Schema::hasColumn('payroll_items', 'total_employer_government_contributions')) {
                $table->decimal('total_employer_government_contributions', 12, 2)->default(0)->after('total_employee_government_deductions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'finalized_by')) {
                $table->dropColumn('finalized_by');
            }
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $columns = [
                'taxable_compensation',
                'sss_employee',
                'sss_employer',
                'philhealth_employee',
                'philhealth_employer',
                'pagibig_employee',
                'pagibig_employer',
                'withholding_tax',
                'total_employee_government_deductions',
                'total_employer_government_contributions',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('payroll_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
