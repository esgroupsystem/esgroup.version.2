<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_biometrics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('biometric_company_id')
                ->nullable()
                ->constrained('biometric_companies')
                ->nullOnDelete();

            $table->string('source_key')->unique();

            $table->string('source_crosschex_account')->nullable()->index();
            $table->string('source_crosschex_account_name')->nullable();
            $table->string('source_crosschex_id')->nullable()->index();
            $table->string('source_employee_id')->nullable()->index();
            $table->string('source_employee_no')->nullable()->index();
            $table->string('source_employee_name')->nullable();

            $table->string('display_employee_no')->nullable()->index();
            $table->string('display_name')->index();

            $table->string('device_sn')->nullable();
            $table->string('device_name')->nullable();

            $table->timestamp('last_check_time')->nullable()->index();
            $table->unsignedInteger('total_logs')->default(0);

            $table->enum('employment_status', ['active', 'inactive'])
                ->default('active')
                ->index();

            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['employment_status', 'biometric_company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_biometrics');
    }
};
