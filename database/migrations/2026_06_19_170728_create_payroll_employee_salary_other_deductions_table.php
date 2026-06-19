<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payroll_employee_salary_other_deductions')) {
            return;
        }

        Schema::create('payroll_employee_salary_other_deductions', function (Blueprint $table) {
            $table->id();

            // ✅ 1. CREATE COLUMN FIRST
            $table->unsignedBigInteger('payroll_employee_salary_id');

            $table->string('name');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('payment_amount', 12, 2)->default(0);
            $table->string('deduction_schedule')->default('none');

            $table->date('start_date')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // indexes
            $table->index('payroll_employee_salary_id', 'pesod_salary_id_index');
            $table->index('deduction_schedule', 'pesod_schedule_index');

            // ✅ 2. ADD FOREIGN KEY LAST
            $table->foreign('payroll_employee_salary_id', 'pes_od_ps_id_fk')
                ->references('id')
                ->on('payroll_employee_salaries')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_employee_salary_other_deductions');
    }
};
