<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_employee_salaries', function (Blueprint $table) {
            $table->decimal('sss_loan', 12, 2)->default(0)->after('absent_deduction_per_day');
            $table->decimal('pagibig_loan', 12, 2)->default(0)->after('sss_loan');
            $table->decimal('vale', 12, 2)->default(0)->after('pagibig_loan');
            $table->decimal('other_loans', 12, 2)->default(0)->after('vale');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_employee_salaries', function (Blueprint $table) {
            $table->dropColumn([
                'sss_loan',
                'pagibig_loan',
                'vale',
                'other_loans'
            ]);
        });
    }
};