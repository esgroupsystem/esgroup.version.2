<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_orders_maintenance', function (Blueprint $table) {
            $table->id();

            $table->string('job_order_no')->unique();

            $table->foreignId('bus_id')
                ->constrained('buses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('bus_no_snapshot')->nullable();
            $table->string('plate_no_snapshot')->nullable();
            $table->string('company_snapshot')->nullable();
            $table->string('garage_snapshot')->nullable();

            $table->string('full_name')->nullable();

            $table->text('description_of_work');

            $table->unsignedInteger('odometer_reading')->nullable();
            $table->unsignedInteger('last_odometer_reading')->nullable();
            $table->integer('odometer_difference')->nullable();

            $table->boolean('is_odometer_lower_than_last')->default(false);

            $table->string('status')->default('open');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['bus_id', 'status']);
            $table->index(['bus_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};
