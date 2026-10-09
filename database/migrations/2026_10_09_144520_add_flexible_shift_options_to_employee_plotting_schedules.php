<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flexible Shift (Custom): one or more exact shift-time options an employee may clock into,
     * e.g. [{"time_in":"08:00","time_out":"17:00"},{"time_in":"09:00","time_out":"18:00"}].
     * The actual time in is matched to the closest option. Null/single-entry legacy rows fall
     * back to the time_in/time_out columns, which mirror the first option.
     */
    public function up(): void
    {
        Schema::table('employee_plotting_schedules', function (Blueprint $table): void {
            $table->json('flexible_shift_options')->nullable()->after('flexible_mode');
        });
    }

    public function down(): void
    {
        Schema::table('employee_plotting_schedules', function (Blueprint $table): void {
            $table->dropColumn('flexible_shift_options');
        });
    }
};
