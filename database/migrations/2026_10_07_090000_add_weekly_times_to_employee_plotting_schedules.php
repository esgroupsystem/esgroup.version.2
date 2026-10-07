<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-weekday times on the permanent Work Schedule ("different time per day").
     * Null = the same time every working day (all existing schedules stay as they are).
     * Shape: {"Monday": {"time_in": "09:00", "time_out": "18:00", "workday_type": "eight_hours"}, ...}
     */
    public function up(): void
    {
        Schema::table('employee_plotting_schedules', function (Blueprint $table): void {
            $table->json('weekly_times')->nullable()->after('time_out');
        });
    }

    public function down(): void
    {
        Schema::table('employee_plotting_schedules', function (Blueprint $table): void {
            $table->dropColumn('weekly_times');
        });
    }
};
