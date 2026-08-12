<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_benefit_settlements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnDelete();
            $table->foreignId('payroll_item_id')->unique()->constrained('payroll_items')->cascadeOnDelete();
            $table->foreignId('employee_biometric_id')->nullable()->constrained('employee_biometrics')->nullOnDelete();

            // auto_cap | employer_advance | collect_full
            $table->string('mode', 40)->default('auto_cap');

            // These are employee cash reimbursements/credits only. They never
            // cancel the statutory monthly contribution liability.
            $table->decimal('sss_employee_reimbursement', 15, 2)->default(0);
            $table->decimal('philhealth_employee_reimbursement', 15, 2)->default(0);
            $table->decimal('pagibig_employee_reimbursement', 15, 2)->default(0);

            $table->text('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['payroll_id', 'mode'], 'payroll_benefit_settlements_payroll_mode_idx');
            $table->index(['employee_biometric_id', 'created_at'], 'payroll_benefit_settlements_employee_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_benefit_settlements');
    }
};
