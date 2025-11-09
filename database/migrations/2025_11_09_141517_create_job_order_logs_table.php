<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_order_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('joborder_id')->constrained('job_orders')->cascadeOnDelete();
            $table->string('action');                   // e.g. created, updated, status_changed
            $table->json('meta')->nullable();           // any extra data
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_order_logs');
    }
};
