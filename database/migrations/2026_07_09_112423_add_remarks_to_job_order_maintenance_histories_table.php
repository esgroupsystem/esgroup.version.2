<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_order_maintenance_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_order_maintenance_id')
                ->constrained('job_orders_maintenance')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('action', 100);

            $table->text('remarks')->nullable();

            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();

            $table->timestamps();

            $table->index('job_order_maintenance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_order_maintenance_histories');
    }
};
