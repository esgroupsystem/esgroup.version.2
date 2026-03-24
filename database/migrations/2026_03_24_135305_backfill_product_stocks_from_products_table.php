<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $mainOffice = DB::table('locations')->where('code', 'MAIN')->first();

        if (!$mainOffice) {
            return;
        }

        $products = DB::table('products')->get();

        foreach ($products as $product) {
            DB::table('product_stocks')->updateOrInsert(
                [
                    'product_id' => $product->id,
                    'location_id' => $mainOffice->id,
                ],
                [
                    'qty' => $product->stock_qty ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        $mainOffice = DB::table('locations')->where('code', 'MAIN')->first();

        if ($mainOffice) {
            DB::table('product_stocks')->where('location_id', $mainOffice->id)->delete();
        }
    }
};