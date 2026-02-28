<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_histories', function (Blueprint $table) {
            $table->date('suspension_start_date')->nullable()->after('end_date');
            $table->date('suspension_end_date')->nullable()->after('suspension_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_histories', function (Blueprint $table) {
            $table->dropColumn(['suspension_start_date', 'suspension_end_date']);
        });
    }
};
