<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_inventory_items', function (Blueprint $table) {
            $table->id();

            $table->string('item_name');
            $table->string('category')->nullable();      // CCTV, Network, Computer Parts, Printer, Accessories
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('part_number')->nullable();

            $table->string('unit')->default('pcs');      // pcs, box, roll, meter, set
            $table->integer('stock_qty')->default(0);
            $table->integer('minimum_stock')->default(0);

            $table->text('description')->nullable();
            $table->string('location')->nullable();      // IT Room, Main Office, Branch, etc.
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_inventory_items');
    }
};
