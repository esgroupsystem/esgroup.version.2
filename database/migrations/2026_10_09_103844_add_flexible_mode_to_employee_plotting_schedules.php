<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flexible Shift sub-mode: null (Regular Shift) | "anytime" | "condition" | "custom".
     * Null/anytime keeps today's behaviour (no fixed time, total clock hours only).
     */
    public function up(): void
    {
        Schema::table('employee_plotting_schedules', function (Blueprint $table): void {
            $table->string('flexible_mode', 20)->nullable()->after('shift_name');
        });
    }

    public function down(): void
    {
        Schema::table('employee_plotting_schedules', function (Blueprint $table): void {
            $table->dropColumn('flexible_mode');
        });
    }
};
