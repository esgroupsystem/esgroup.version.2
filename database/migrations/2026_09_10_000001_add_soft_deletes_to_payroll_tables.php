<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
                if (! Schema::hasColumn('payrolls', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (Schema::hasTable('payroll_attendance_adjustments')) {
            Schema::table('payroll_attendance_adjustments', function (Blueprint $table) {
                if (! Schema::hasColumn('payroll_attendance_adjustments', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payrolls')) {
            Schema::table('payrolls', function (Blueprint $table) {
                if (Schema::hasColumn('payrolls', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }

        if (Schema::hasTable('payroll_attendance_adjustments')) {
            Schema::table('payroll_attendance_adjustments', function (Blueprint $table) {
                if (Schema::hasColumn('payroll_attendance_adjustments', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
};
