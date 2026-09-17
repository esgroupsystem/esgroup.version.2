<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payroll_attendance_adjustments')) {
            return;
        }

        Schema::table('payroll_attendance_adjustments', function (Blueprint $table) {
            if (! Schema::hasColumn('payroll_attendance_adjustments', 'amount')) {
                $table->decimal('amount', 12, 2)
                    ->nullable()
                    ->after('approved_minutes');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('payroll_attendance_adjustments')) {
            return;
        }

        Schema::table('payroll_attendance_adjustments', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_attendance_adjustments', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }
};
