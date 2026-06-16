<?php

namespace App\Services\Maintenance;

use App\Models\PartsOut;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PartsOutRollbackService
{
    public function rollback(int $partsOutId, ?string $reason = null): void
    {
        DB::transaction(function () use ($partsOutId, $reason) {
            $partsOut = PartsOut::query()
                ->with(['items.product'])
                ->whereKey($partsOutId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($partsOut->status === 'rolled_back') {
                throw new RuntimeException('This Parts Out record is already rolled back.');
            }

            if ($partsOut->status !== 'posted') {
                throw new RuntimeException('Only posted Parts Out records can be rolled back.');
            }

            if (! $partsOut->location_id) {
                throw new RuntimeException('Parts Out location is missing. Cannot return stock.');
            }

            foreach ($partsOut->items as $item) {
                $qtyUsed = (int) $item->qty_used;

                if ($qtyUsed <= 0) {
                    continue;
                }

                $product = Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    throw new RuntimeException('Product not found during rollback.');
                }

                $productStock = ProductStock::query()
                    ->where('product_id', $item->product_id)
                    ->where('location_id', $partsOut->location_id)
                    ->lockForUpdate()
                    ->first();

                if (! $productStock) {
                    $productStock = ProductStock::create([
                        'product_id' => $item->product_id,
                        'location_id' => $partsOut->location_id,
                        'qty' => 0,
                    ]);
                }

                $stockBefore = (int) $productStock->qty;
                $stockAfter = $stockBefore + $qtyUsed;

                $productStock->update([
                    'qty' => $stockAfter,
                ]);

                $product->update([
                    'stock_qty' => ProductStock::query()
                        ->where('product_id', $product->id)
                        ->sum('qty'),
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'location_id' => $partsOut->location_id,
                    'reference_type' => 'parts_out_rollback',
                    'reference_id' => $partsOut->id,
                    'movement_type' => 'in',
                    'qty' => $qtyUsed,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'transaction_date' => now(),
                    'remarks' => 'Rollback of Parts Out #'.$partsOut->parts_out_number,
                    'created_by' => Auth::id(),
                ]);
            }

            $partsOut->update([
                'status' => 'rolled_back',
                'rolled_back_at' => now(),
                'rolled_back_by' => Auth::id(),
                'rollback_reason' => $reason,
            ]);

            $partsOut->delete();
        });
    }
}
