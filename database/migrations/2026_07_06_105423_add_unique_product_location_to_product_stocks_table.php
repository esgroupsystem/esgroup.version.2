<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_stocks', function (Blueprint $table): void {
            $table->unique(['product_id', 'location_id'], 'product_stocks_product_location_unique');
        });
    }

    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table): void {
            $table->dropUnique('product_stocks_product_location_unique');
        });
    }
};
