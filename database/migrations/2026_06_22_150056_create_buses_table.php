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
        Schema::create('buses', function (Blueprint $table): void {
            $table->id();

            $table->string('bus_no', 50);
            $table->string('plate_no', 50)->nullable();
            $table->string('company', 100)->nullable();
            $table->string('garage', 100)->nullable();

            $table->string('chassis_number', 100)->nullable();
            $table->string('engine_number', 100)->nullable();
            $table->string('case_number', 100)->nullable();

            $table->timestamps();

            $table->index('bus_no');
            $table->index('plate_no');
            $table->index('company');
            $table->index('garage');

            /*
             | This prevents exact duplicate bus + plate records.
             | Example: 471901 + NBT1539 should not be inserted twice.
             */
            $table->unique(['bus_no', 'plate_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
