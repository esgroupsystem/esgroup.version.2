<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cctv_concern_items', function (Blueprint $table) {
            $table->id();

            // ✅ MATCHES cctv_job_orders.id (BIGINT)
            $table->unsignedBigInteger('cctv_concern_id');

            // ✅ matches it_inventory_items.id
            $table->unsignedBigInteger('it_inventory_item_id');

            $table->integer('qty_used')->default(1);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('cctv_concern_id');
            $table->index('it_inventory_item_id');

            $table->foreign('cctv_concern_id')
                ->references('id')
                ->on('cctv_job_orders')
                ->onDelete('cascade');

            $table->foreign('it_inventory_item_id')
                ->references('id')
                ->on('it_inventory_items')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cctv_concern_items');
    }
};
