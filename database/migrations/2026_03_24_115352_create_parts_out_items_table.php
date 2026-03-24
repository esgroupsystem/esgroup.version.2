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
        Schema::create('parts_out_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parts_out_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('qty_used');
            $table->integer('stock_before')->default(0);
            $table->integer('stock_after')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('parts_out_id')->references('id')->on('parts_outs')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts_out_items');
    }
};
