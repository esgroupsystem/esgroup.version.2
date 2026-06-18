<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_attendance_adjustments', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_attendance_adjustments', 'date_from')) {
                $table->date('date_from')->nullable()->after('work_date');
            }

            if (! Schema::hasColumn('payroll_attendance_adjustments', 'date_to')) {
                $table->date('date_to')->nullable()->after('date_from');
            }

            if (! Schema::hasColumn('payroll_attendance_adjustments', 'offset_source_date')) {
                $table->date('offset_source_date')->nullable()->after('adjusted_day_type');
            }

            if (! Schema::hasColumn('payroll_attendance_adjustments', 'offset_source_time_in')) {
                $table->time('offset_source_time_in')->nullable()->after('offset_source_date');
            }

            if (! Schema::hasColumn('payroll_attendance_adjustments', 'offset_source_time_out')) {
                $table->time('offset_source_time_out')->nullable()->after('offset_source_time_in');
            }

            if (! Schema::hasColumn('payroll_attendance_adjustments', 'offset_source_logs')) {
                $table->json('offset_source_logs')->nullable()->after('offset_source_time_out');
            }

            $table->index(['biometric_employee_id', 'work_date'], 'paa_employee_work_date_index');
            $table->index(['adjustment_type', 'work_date'], 'paa_type_work_date_index');
            $table->index(['date_from', 'date_to'], 'paa_date_range_index');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_attendance_adjustments', function (Blueprint $table) {
            $table->dropIndex('paa_employee_work_date_index');
            $table->dropIndex('paa_type_work_date_index');
            $table->dropIndex('paa_date_range_index');

            $table->dropColumn([
                'date_from',
                'date_to',
                'offset_source_date',
                'offset_source_time_in',
                'offset_source_time_out',
                'offset_source_logs',
            ]);
        });
    }
};
