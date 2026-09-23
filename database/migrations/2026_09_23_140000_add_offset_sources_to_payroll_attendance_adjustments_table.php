<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One Offset request may pool excess time from several earlier source
     * dates (e.g. 1 extra hour on each of Mon-Fri) to cover one target date.
     * Each entry: {date, minutes, time_in, time_out}. The legacy single
     * offset_source_date column keeps the earliest source date.
     */
    public function up(): void
    {
        Schema::table('payroll_attendance_adjustments', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_attendance_adjustments', 'offset_sources')) {
                $table->json('offset_sources')
                    ->nullable()
                    ->after('offset_source_logs');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll_attendance_adjustments', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_attendance_adjustments', 'offset_sources')) {
                $table->dropColumn('offset_sources');
            }
        });
    }
};
