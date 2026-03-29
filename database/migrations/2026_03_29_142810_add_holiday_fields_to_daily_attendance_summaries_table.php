<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_attendance_summaries', function (Blueprint $table) {

            if (! Schema::hasColumn('daily_attendance_summaries', 'holiday_name')) {
                $table->string('holiday_name')->nullable()->after('is_holiday');
            }

            if (! Schema::hasColumn('daily_attendance_summaries', 'holiday_type')) {
                $table->string('holiday_type')->nullable()->after('holiday_name');
            }

            if (! Schema::hasColumn('daily_attendance_summaries', 'holiday_worked_multiplier')) {
                $table->decimal('holiday_worked_multiplier', 8, 2)->nullable()->after('holiday_type');
            }

            if (! Schema::hasColumn('daily_attendance_summaries', 'holiday_not_worked_multiplier')) {
                $table->decimal('holiday_not_worked_multiplier', 8, 2)->nullable()->after('holiday_worked_multiplier');
            }

        });
    }

    public function down(): void
    {
        Schema::table('daily_attendance_summaries', function (Blueprint $table) {
            $table->dropColumn([
                'holiday_name',
                'holiday_type',
                'holiday_worked_multiplier',
                'holiday_not_worked_multiplier',
            ]);
        });
    }
};
