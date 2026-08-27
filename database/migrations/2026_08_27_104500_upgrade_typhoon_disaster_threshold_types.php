<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payroll_attendance_adjustments')
            && Schema::hasColumn('payroll_attendance_adjustments', 'adjustment_type')) {
            DB::table('payroll_attendance_adjustments')
                ->where('adjustment_type', 'typhoon_disaster')
                ->update(['adjustment_type' => 'typhoon_disaster_3h']);
        }

        if (Schema::hasTable('daily_attendance_summaries')
            && Schema::hasColumn('daily_attendance_summaries', 'adjustment_type')) {
            DB::table('daily_attendance_summaries')
                ->where('adjustment_type', 'typhoon_disaster')
                ->update(['adjustment_type' => 'typhoon_disaster_3h']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payroll_attendance_adjustments')
            && Schema::hasColumn('payroll_attendance_adjustments', 'adjustment_type')) {
            DB::table('payroll_attendance_adjustments')
                ->where('adjustment_type', 'typhoon_disaster_3h')
                ->update(['adjustment_type' => 'typhoon_disaster']);
        }

        if (Schema::hasTable('daily_attendance_summaries')
            && Schema::hasColumn('daily_attendance_summaries', 'adjustment_type')) {
            DB::table('daily_attendance_summaries')
                ->where('adjustment_type', 'typhoon_disaster_3h')
                ->update(['adjustment_type' => 'typhoon_disaster']);
        }
    }
};
