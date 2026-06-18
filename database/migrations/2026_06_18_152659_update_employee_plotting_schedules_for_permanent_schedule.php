<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_plotting_schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_plotting_schedules', 'day_off')) {
                $table->string('day_off', 20)->nullable()->after('status');
            }
        });

        if (Schema::hasColumn('employee_plotting_schedules', 'work_date')) {
            Schema::table('employee_plotting_schedules', function (Blueprint $table) {
                $table->date('work_date')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('employee_plotting_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('employee_plotting_schedules', 'day_off')) {
                $table->dropColumn('day_off');
            }
        });
    }
};
