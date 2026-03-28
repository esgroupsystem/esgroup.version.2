<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_attendance_adjustments', function (Blueprint $table) {
            $table->id();

            $table->string('biometric_employee_id')->nullable();
            $table->string('employee_no')->nullable();
            $table->string('employee_name');

            $table->date('work_date');

            $table->enum('adjustment_type', [
                'change_schedule',
                'change_time',
                'offset',
                'rest_day_work',
                'holiday_work',
                'official_business',
                'training',
                'manual_time_in_out',
                'manual_present',
                'manual_absent',
            ]);

            $table->time('adjusted_time_in')->nullable();
            $table->time('adjusted_time_out')->nullable();

            $table->string('adjusted_day_type')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->boolean('ignore_late')->default(false);
            $table->boolean('ignore_undertime')->default(false);

            $table->text('reason')->nullable();
            $table->text('remarks')->nullable();

            $table->foreignId('encoded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('encoded_at')->nullable();

            $table->timestamps();

            $table->index(['biometric_employee_id', 'work_date'], 'pay_att_adj_bio_emp_date_idx');
            $table->index(['employee_no', 'work_date'], 'pay_att_adj_empno_date_idx');
            $table->index(['work_date', 'adjustment_type'], 'pay_att_adj_date_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_attendance_adjustments');
    }
};