<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_plotting_schedules', function (Blueprint $table) {
            $table->string('adjusted_shift_name')->nullable()->after('shift_name');
            $table->time('adjusted_time_in')->nullable()->after('time_out');
            $table->time('adjusted_time_out')->nullable()->after('adjusted_time_in');

            $table->boolean('is_adjusted')->default(false)->after('remarks');
            $table->text('adjustment_reason')->nullable()->after('is_adjusted');
            $table->unsignedBigInteger('adjusted_by')->nullable()->after('adjustment_reason');
            $table->timestamp('adjusted_at')->nullable()->after('adjusted_by');
        });
    }

    public function down(): void
    {
        Schema::table('employee_plotting_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'adjusted_shift_name',
                'adjusted_time_in',
                'adjusted_time_out',
                'is_adjusted',
                'adjustment_reason',
                'adjusted_by',
                'adjusted_at',
            ]);
        });
    }
};
