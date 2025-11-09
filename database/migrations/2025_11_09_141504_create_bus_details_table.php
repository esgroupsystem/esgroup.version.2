<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bus_details', function (Blueprint $table) {
            $table->id();
            $table->string('garage')->nullable(false);
            $table->string('name')->nullable(false);
            $table->string('body_number')->unique();
            $table->string('plate_number')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus_details');
    }
};
