<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->date('actual_date');   // real holiday date
            $table->date('observed_date'); // date used by payroll/attendance

            $table->enum('holiday_type', ['regular', 'special']);
            $table->boolean('is_moved')->default(false);

            // Multipliers used by payroll
            // Example:
            // regular holiday not worked = 1.00
            // regular holiday worked     = 2.00
            // special holiday not worked = 0.00 (legal default) OR 1.00 (your company rule)
            // special holiday worked     = 1.30
            $table->decimal('not_worked_multiplier', 8, 2)->default(0.00);
            $table->decimal('worked_multiplier', 8, 2)->default(1.00);

            $table->string('source_proclamation')->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['observed_date', 'name']);
            $table->index(['observed_date', 'holiday_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
