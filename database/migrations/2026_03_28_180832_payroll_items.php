<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnDelete();

            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('biometric_employee_id')->nullable();
            $table->string('employee_no')->nullable();
            $table->string('employee_name');

            $table->decimal('monthly_rate', 12, 2)->default(0);
            $table->decimal('daily_rate', 12, 2)->default(0);
            $table->decimal('hourly_rate', 12, 2)->default(0);
            $table->decimal('minute_rate', 12, 4)->default(0);

            $table->decimal('total_worked_days', 8, 2)->default(0);
            $table->decimal('total_payable_days', 8, 2)->default(0);
            $table->decimal('total_payable_hours', 8, 2)->default(0);

            $table->integer('total_worked_minutes')->default(0);
            $table->integer('total_late_minutes')->default(0);
            $table->integer('total_undertime_minutes')->default(0);
            $table->integer('total_overtime_minutes')->default(0);

            $table->integer('total_absent_days')->default(0);
            $table->integer('total_rest_day_worked')->default(0);
            $table->integer('total_holiday_worked')->default(0);
            $table->integer('total_leave_days')->default(0);

            $table->decimal('gross_pay', 12, 2)->default(0);
            $table->decimal('late_deduction', 12, 2)->default(0);
            $table->decimal('undertime_deduction', 12, 2)->default(0);
            $table->decimal('absence_deduction', 12, 2)->default(0);
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('holiday_pay', 12, 2)->default(0);
            $table->decimal('rest_day_pay', 12, 2)->default(0);
            $table->decimal('leave_pay', 12, 2)->default(0);

            $table->decimal('other_additions', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);

            $table->decimal('net_pay', 12, 2)->default(0);

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['payroll_id', 'employee_no'], 'payroll_items_payroll_empno_idx');
            $table->index(['payroll_id', 'biometric_employee_id'], 'payroll_items_payroll_bio_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};