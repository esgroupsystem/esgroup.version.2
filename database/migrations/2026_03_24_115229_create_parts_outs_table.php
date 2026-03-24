<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts_outs', function (Blueprint $table) {
            $table->id();

            $table->string('parts_out_number')->unique();

            $table->unsignedBigInteger('vehicle_id')->nullable();

            $table->string('mechanic_name');
            $table->string('requested_by')->nullable();
            $table->date('issued_date');

            $table->string('job_order_no')->nullable();
            $table->string('odometer')->nullable();

            $table->text('purpose')->nullable();
            $table->text('remarks')->nullable();

            $table->enum('status', ['posted', 'cancelled'])->default('posted');

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('bus_details')
                ->nullOnDelete();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts_outs');
    }
};
