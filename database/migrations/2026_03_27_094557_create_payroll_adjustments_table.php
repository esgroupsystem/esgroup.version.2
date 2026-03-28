<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_entry_id')
                ->constrained('payroll_entries')
                ->cascadeOnDelete();

            $table->string('type');
            $table->string('label');
            $table->decimal('amount', 12, 2);
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_adjustments');
    }
};
