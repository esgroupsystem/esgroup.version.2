<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_plotting_schedules', function (Blueprint $table) {
            $table->id();

            $table->string('crosschex_id')->nullable();
            $table->string('biometric_employee_id')->nullable();
            $table->string('employee_no')->nullable();
            $table->string('employee_name');
            $table->date('work_date');

            $table->string('shift_name')->nullable();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->integer('grace_minutes')->default(15);

            $table->enum('status', ['scheduled', 'rest_day', 'leave', 'holiday'])->default('scheduled');
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['employee_no', 'work_date'], 'eps_empno_workdate_idx');
            $table->index(['biometric_employee_id', 'work_date'], 'eps_bio_workdate_idx');
            $table->index('work_date', 'eps_workdate_idx');
            $table->index('status', 'eps_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_plotting_schedules');
    }
};
