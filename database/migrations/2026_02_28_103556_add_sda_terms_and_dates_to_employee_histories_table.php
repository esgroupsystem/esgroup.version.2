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
            $table->unsignedInteger('sda_terms')->nullable()->after('sda_amount');
            $table->date('sda_start_date')->nullable()->after('sda_terms');
            $table->date('sda_end_date')->nullable()->after('sda_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_histories', function (Blueprint $table) {
            $table->dropColumn(['sda_terms', 'sda_start_date', 'sda_end_date']);
        });
    }
};
