<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('request_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('garage_group')->nullable();

            $table->string('module', 50);
            $table->string('action', 50);
            $table->string('auditable_type', 191)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();

            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
            $table->foreignId('payroll_item_id')->nullable()->constrained('payroll_items')->nullOnDelete();
            $table->foreignId('employee_biometric_id')->nullable()->constrained('employee_biometrics')->nullOnDelete();
            $table->unsignedBigInteger('employee_id')->nullable();

            $table->string('description', 500)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('context')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('request_id', 'payroll_audit_request_idx');
            $table->index(['module', 'created_at'], 'payroll_audit_module_created_idx');
            $table->index(['user_id', 'created_at'], 'payroll_audit_user_created_idx');
            $table->index(['payroll_id', 'created_at'], 'payroll_audit_payroll_created_idx');
            $table->index(['employee_biometric_id', 'created_at'], 'payroll_audit_employee_created_idx');
            $table->index(['garage_group', 'created_at'], 'payroll_audit_group_created_idx');
            $table->index(['auditable_type', 'auditable_id'], 'payroll_audit_auditable_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_audit_logs');
    }
};
