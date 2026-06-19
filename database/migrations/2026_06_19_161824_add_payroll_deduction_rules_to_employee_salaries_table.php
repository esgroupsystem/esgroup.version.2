<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $tableName = 'payroll_employee_salaries';

    public function up(): void
    {
        $this->addColumnIfMissing('sss_contribution_cutoff', function (Blueprint $table) {
            $table->string('sss_contribution_cutoff')->default('first_cutoff');
        });

        $this->addColumnIfMissing('pagibig_contribution_cutoff', function (Blueprint $table) {
            $table->string('pagibig_contribution_cutoff')->default('second_cutoff');
        });

        $this->addColumnIfMissing('philhealth_contribution_cutoff', function (Blueprint $table) {
            $table->string('philhealth_contribution_cutoff')->default('second_cutoff');
        });

        $this->addColumnIfMissing('allowance_release_schedule', function (Blueprint $table) {
            $table->string('allowance_release_schedule')->default('every_cutoff');
        });

        $this->addColumnIfMissing('sim_load_allowance', function (Blueprint $table) {
            $table->decimal('sim_load_allowance', 12, 2)->default(0);
        });

        $this->addColumnIfMissing('sim_load_release_schedule', function (Blueprint $table) {
            $table->string('sim_load_release_schedule')->default('every_cutoff');
        });

        $this->addLoanColumns('sss_loan');
        $this->addLoanColumns('pagibig_loan');
        $this->addLoanColumns('philhealth_loan');
        $this->addLoanColumns('cash_advance');
        $this->addLoanColumns('other_loan');

        DB::table($this->tableName)->update([
            'sss_loan_total_amount' => DB::raw('COALESCE(sss_loan, 0)'),
            'sss_loan_payment_amount' => DB::raw('COALESCE(sss_loan, 0)'),
            'sss_loan_deduction_schedule' => DB::raw("CASE WHEN COALESCE(sss_loan, 0) > 0 THEN 'every_cutoff' ELSE 'none' END"),

            'pagibig_loan_total_amount' => DB::raw('COALESCE(pagibig_loan, 0)'),
            'pagibig_loan_payment_amount' => DB::raw('COALESCE(pagibig_loan, 0)'),
            'pagibig_loan_deduction_schedule' => DB::raw("CASE WHEN COALESCE(pagibig_loan, 0) > 0 THEN 'every_cutoff' ELSE 'none' END"),

            'cash_advance_total_amount' => DB::raw('COALESCE(vale, 0)'),
            'cash_advance_payment_amount' => DB::raw('COALESCE(vale, 0)'),
            'cash_advance_deduction_schedule' => DB::raw("CASE WHEN COALESCE(vale, 0) > 0 THEN 'every_cutoff' ELSE 'none' END"),

            'other_loan_total_amount' => DB::raw('COALESCE(other_loans, 0)'),
            'other_loan_payment_amount' => DB::raw('COALESCE(other_loans, 0)'),
            'other_loan_deduction_schedule' => DB::raw("CASE WHEN COALESCE(other_loans, 0) > 0 THEN 'every_cutoff' ELSE 'none' END"),
        ]);
    }

    public function down(): void
    {
        $columns = [
            'sss_contribution_cutoff',
            'pagibig_contribution_cutoff',
            'philhealth_contribution_cutoff',
            'allowance_release_schedule',
            'sim_load_allowance',
            'sim_load_release_schedule',

            'sss_loan_total_amount',
            'sss_loan_payment_amount',
            'sss_loan_deduction_schedule',
            'sss_loan_start_date',

            'pagibig_loan_total_amount',
            'pagibig_loan_payment_amount',
            'pagibig_loan_deduction_schedule',
            'pagibig_loan_start_date',

            'philhealth_loan_total_amount',
            'philhealth_loan_payment_amount',
            'philhealth_loan_deduction_schedule',
            'philhealth_loan_start_date',

            'cash_advance_total_amount',
            'cash_advance_payment_amount',
            'cash_advance_deduction_schedule',
            'cash_advance_start_date',

            'other_loan_total_amount',
            'other_loan_payment_amount',
            'other_loan_deduction_schedule',
            'other_loan_start_date',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn($this->tableName, $column)) {
                Schema::table($this->tableName, function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    private function addLoanColumns(string $prefix): void
    {
        $this->addColumnIfMissing("{$prefix}_total_amount", function (Blueprint $table) use ($prefix) {
            $table->decimal("{$prefix}_total_amount", 12, 2)->default(0);
        });

        $this->addColumnIfMissing("{$prefix}_payment_amount", function (Blueprint $table) use ($prefix) {
            $table->decimal("{$prefix}_payment_amount", 12, 2)->default(0);
        });

        $this->addColumnIfMissing("{$prefix}_deduction_schedule", function (Blueprint $table) use ($prefix) {
            $table->string("{$prefix}_deduction_schedule")->default('none');
        });

        $this->addColumnIfMissing("{$prefix}_start_date", function (Blueprint $table) use ($prefix) {
            $table->date("{$prefix}_start_date")->nullable();
        });
    }

    private function addColumnIfMissing(string $column, callable $definition): void
    {
        if (! Schema::hasColumn($this->tableName, $column)) {
            Schema::table($this->tableName, function (Blueprint $table) use ($definition) {
                $definition($table);
            });
        }
    }
};
