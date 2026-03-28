<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_employee_salaries', function (Blueprint $table) {
            $table->id();

            $table->string('biometric_employee_id')->index();
            $table->string('employee_no')->nullable()->index();
            $table->string('employee_name');
            $table->string('crosschex_id')->nullable();

            $table->enum('rate_type', ['daily', 'monthly'])->default('daily');

            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('allowance', 12, 2)->default(0);
            $table->decimal('ot_rate_per_hour', 12, 2)->default(0);
            $table->decimal('late_deduction_per_minute', 12, 4)->default(0);
            $table->decimal('undertime_deduction_per_minute', 12, 4)->default(0);
            $table->decimal('absent_deduction_per_day', 12, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique(['biometric_employee_id'], 'pes_bio_emp_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_employee_salaries');
    }
};
