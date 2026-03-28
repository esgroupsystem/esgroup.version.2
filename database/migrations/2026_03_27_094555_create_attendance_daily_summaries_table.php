<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_daily_summaries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_employee_id')
                ->constrained('payroll_employees')
                ->cascadeOnDelete();

            $table->date('work_date')->index();
            $table->dateTime('time_in')->nullable();
            $table->dateTime('time_out')->nullable();

            $table->integer('worked_minutes')->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('undertime_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);

            $table->string('status')->default('present');
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique(['payroll_employee_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_daily_summaries');
    }
};
