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
        Schema::create('claims', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->string('claim_type'); // SSS, MATERNITY, PATERNITY
            $table->string('status')->default('Draft');

            $table->string('reference_no')->nullable();

            $table->date('date_of_notification')->nullable();
            $table->date('date_filed')->nullable();
            $table->date('approval_date')->nullable();
            $table->date('fund_request_date')->nullable();
            $table->date('fund_released_date')->nullable();

            $table->decimal('amount', 12, 2)->nullable();
            $table->text('remarks')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['employee_id', 'claim_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
