<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payroll_report_logs')) {
            return;
        }

        Schema::create('payroll_report_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
            $table->foreignId('payroll_item_id')->nullable()->constrained('payroll_items')->nullOnDelete();
            $table->unsignedBigInteger('employee_id')->nullable()->index();
            $table->string('biometric_employee_id')->nullable()->index();
            $table->string('employee_no')->nullable()->index();
            $table->string('employee_name')->nullable();
            $table->string('report_type', 50)->index();
            $table->unsignedTinyInteger('cutoff_month')->nullable();
            $table->unsignedSmallInteger('cutoff_year')->nullable();
            $table->string('cutoff_type', 20)->nullable();
            $table->unsignedTinyInteger('contribution_month')->nullable()->index();
            $table->unsignedSmallInteger('contribution_year')->nullable()->index();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('basis_amount', 15, 2)->default(0);
            $table->decimal('computed_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('draft');
            $table->text('remarks')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Do not drop report logs automatically.
    }
};
