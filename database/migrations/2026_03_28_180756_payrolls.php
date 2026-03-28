<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();

            $table->string('payroll_number')->unique();

            $table->unsignedTinyInteger('cutoff_month');
            $table->unsignedSmallInteger('cutoff_year');
            $table->enum('cutoff_type', ['first', 'second']);

            $table->date('period_start');
            $table->date('period_end');

            $table->string('status')->default('draft'); // draft, finalized, cancelled
            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('finalized_at')->nullable();

            $table->timestamps();

            $table->index(['cutoff_year', 'cutoff_month', 'cutoff_type'], 'payroll_cutoff_idx');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};