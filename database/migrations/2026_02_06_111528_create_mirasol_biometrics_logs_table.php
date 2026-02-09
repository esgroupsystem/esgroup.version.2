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
        Schema::create('mirasol_biometrics_logs', function (Blueprint $table) {
            $table->id();
            $table->string('crosschex_id')->nullable()->index();
            $table->unsignedBigInteger('employee_id')->nullable()->index();
            $table->string('employee_no')->nullable()->index();
            $table->string('employee_name')->nullable();
            $table->dateTime('check_time')->nullable()->index();
            $table->string('device_sn')->nullable()->index();
            $table->string('device_name')->nullable();
            $table->string('state')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
            $table->unique(['employee_no', 'check_time', 'device_sn'], 'uniq_emp_time_device');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mirasol_biometrics_logs');
    }
};
