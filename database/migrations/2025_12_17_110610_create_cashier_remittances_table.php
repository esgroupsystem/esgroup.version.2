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
        Schema::create('cashier_remittances', function (Blueprint $table) {
            $table->id();
            $table->string('bus_number');
            $table->string('driver_name');
            $table->string('conductor_name');
            $table->string('dispatcher_name');
            $table->time('time_in');
            $table->time('time_out');
            $table->decimal('total_collection', 10, 2);
            $table->decimal('diesel', 10, 2)->default(0);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_remittances');
    }
};
