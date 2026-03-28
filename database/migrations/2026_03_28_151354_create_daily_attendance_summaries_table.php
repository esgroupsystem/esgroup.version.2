<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_attendance_summaries', function (Blueprint $table) {
            $table->id();

            // optional future link to employee table
            $table->unsignedBigInteger('employee_id')->nullable();

            // actual attendance identity
            $table->string('biometric_employee_id')->nullable();
            $table->string('employee_no')->nullable();
            $table->string('employee_name');

            $table->date('work_date');

            // source references
            $table->unsignedBigInteger('plotting_schedule_id')->nullable();
            $table->unsignedBigInteger('attendance_adjustment_id')->nullable();
            $table->unsignedBigInteger('holiday_id')->nullable();

            // schedule snapshot
            $table->string('crosschex_id')->nullable();
            $table->string('shift_name')->nullable();
            $table->time('scheduled_time_in')->nullable();
            $table->time('scheduled_time_out')->nullable();
            $table->integer('grace_minutes')->default(0);
            $table->string('schedule_status')->nullable();
            $table->text('schedule_remarks')->nullable();

            // biometrics snapshot
            $table->dateTime('actual_time_in')->nullable();
            $table->dateTime('actual_time_out')->nullable();
            $table->integer('raw_log_count')->default(0);
            $table->boolean('has_biometrics')->default(false);
            $table->string('first_log_state')->nullable();
            $table->string('last_log_state')->nullable();

            // holiday flags
            $table->boolean('is_rest_day')->default(false);
            $table->boolean('is_leave')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->string('holiday_type')->nullable();

            // adjustment snapshot
            $table->boolean('has_adjustment')->default(false);
            $table->string('adjustment_type')->nullable();
            $table->time('adjusted_time_in')->nullable();
            $table->time('adjusted_time_out')->nullable();
            $table->string('adjusted_day_type')->nullable();
            $table->boolean('adjustment_is_paid')->default(false);
            $table->boolean('ignore_late')->default(false);
            $table->boolean('ignore_undertime')->default(false);
            $table->text('adjustment_reason')->nullable();
            $table->text('adjustment_remarks')->nullable();

            // computed result
            $table->string('attendance_status')->nullable();
            $table->integer('late_minutes')->default(0);
            $table->integer('undertime_minutes')->default(0);
            $table->integer('worked_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);

            $table->decimal('payable_days', 5, 2)->default(0);
            $table->decimal('payable_hours', 8, 2)->default(0);

            $table->boolean('is_absent')->default(false);
            $table->boolean('is_incomplete_log')->default(false);

            $table->text('remarks')->nullable();
            $table->timestamp('computed_at')->nullable();

            $table->timestamps();

            $table->index(['biometric_employee_id', 'work_date'], 'das_bioid_workdate_idx');
            $table->index(['employee_no', 'work_date'], 'das_empno_workdate_idx');
            $table->index(['employee_name', 'work_date'], 'das_empname_workdate_idx');
            $table->index(['work_date', 'attendance_status'], 'das_workdate_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_attendance_summaries');
    }
};
