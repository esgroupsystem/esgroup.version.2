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
        Schema::table('employees', function (Blueprint $table) {
            $table->date('date_resigned')->nullable()->after('status');
            $table->date('last_duty')->nullable()->after('date_resigned');
            $table->date('clearance_date')->nullable()->after('last_duty');
            $table->string('last_pay_status')->nullable()->after('clearance_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['date_resigned', 'last_duty', 'clearance_date', 'last_pay_status']);
        });
    }
};
