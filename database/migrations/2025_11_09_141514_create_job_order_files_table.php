<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_order_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_orders')->cascadeOnDelete();
            $table->string('file_name')->nullable();
            $table->string('file_remarks')->nullable();
            $table->string('file_notes')->nullable();
            $table->string('file_path')->nullable(); // storage path
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_order_files');
    }
};
