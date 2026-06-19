<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_logs')) {
            Schema::table('payment_logs', function (Blueprint $table) {
                $this->addColumnIfMissing($table, 'payroll_id', fn () => $table->unsignedBigInteger('payroll_id')->nullable()->index());
                $this->addColumnIfMissing($table, 'payroll_item_id', fn () => $table->unsignedBigInteger('payroll_item_id')->nullable()->index());
                $this->addColumnIfMissing($table, 'employee_no', fn () => $table->string('employee_no')->nullable()->index());
                $this->addColumnIfMissing($table, 'employee_name', fn () => $table->string('employee_name')->nullable());
                $this->addColumnIfMissing($table, 'log_type', fn () => $table->string('log_type', 50)->nullable()->index());
                $this->addColumnIfMissing($table, 'source_type', fn () => $table->string('source_type', 50)->nullable()->index());
                $this->addColumnIfMissing($table, 'source_name', fn () => $table->string('source_name')->nullable());
                $this->addColumnIfMissing($table, 'contribution_month', fn () => $table->unsignedTinyInteger('contribution_month')->nullable()->index());
                $this->addColumnIfMissing($table, 'contribution_year', fn () => $table->unsignedSmallInteger('contribution_year')->nullable()->index());
                $this->addColumnIfMissing($table, 'reference', fn () => $table->string('reference')->nullable());
                $this->addColumnIfMissing($table, 'meta', fn () => $table->json('meta')->nullable());
            });

            return;
        }

        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
            $table->foreignId('payroll_item_id')->nullable()->constrained('payroll_items')->nullOnDelete();
            $table->unsignedBigInteger('employee_id')->nullable()->index();
            $table->unsignedBigInteger('payroll_employee_salary_id')->nullable()->index();
            $table->string('biometric_employee_id')->nullable()->index();
            $table->string('employee_no')->nullable()->index();
            $table->string('employee_name')->nullable();
            $table->string('log_type', 50)->index();
            $table->string('source_type', 50)->index();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('source_name')->nullable();
            $table->string('deduction_schedule', 30)->nullable();
            $table->unsignedTinyInteger('cutoff_month')->nullable();
            $table->unsignedSmallInteger('cutoff_year')->nullable();
            $table->string('cutoff_type', 20)->nullable();
            $table->unsignedTinyInteger('contribution_month')->nullable()->index();
            $table->unsignedSmallInteger('contribution_year')->nullable()->index();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('employee_share', 15, 2)->default(0);
            $table->decimal('employer_share', 15, 2)->default(0);
            $table->decimal('balance_before', 15, 2)->nullable();
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->unsignedInteger('payment_no')->nullable();
            $table->unsignedInteger('remaining_payments')->nullable();
            $table->string('reference')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index(['cutoff_year', 'cutoff_month', 'cutoff_type']);
        });
    }

    public function down(): void
    {
        // Do not drop logs automatically.
    }

    private function addColumnIfMissing(Blueprint $table, string $column, callable $callback): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $callback();
        }
    }
};
