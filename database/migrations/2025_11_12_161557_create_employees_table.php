<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('full_name');
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('company')->nullable();
            $table->string('status')->default('Active');
            $table->date('date_hired')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
