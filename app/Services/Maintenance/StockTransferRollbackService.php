<?php

namespace App\Services\Maintenance;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockTransfer;
use Exception;
use Illuminate\Support\Facades\DB;

class StockTransferRollbackService
{
    public function rollback(StockTransfer $stockTransfer, int $userId, ?string $reason = null): StockTransfer
    {
        return DB::transaction(function () use ($stockTransfer, $userId, $reason) {
            $transfer = StockTransfer::query()
                ->whereKey($stockTransfer->id)
                ->lockForUpdate()
                ->firstOrFail();

            $transfer->load([
                'fromLocation',
                'toLocation',
                'items.product',
            ]);

            if ($transfer->status === 'rolled_back' || $transfer->rolled_back_at) {
                throw new Exception('This stock transfer has already been rolled back.');
            }

            if ($transfer->items->isEmpty()) {
                throw new Exception('Cannot rollback because this transfer has no items.');
            }

            foreach ($transfer->items as $item) {
                $qty = (int) $item->qty;

                if ($qty <= 0) {
                    throw new Exception('Invalid rollback quantity detected.');
                }

                $product = Product::query()
                    ->whereKey($item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $toStock = ProductStock::query()
                    ->where('product_id', $item->product_id)
                    ->where('location_id', $transfer->to_location_id)
                    ->lockForUpdate()
                    ->first();

                if (! $toStock || (int) $toStock->qty < $qty) {
                    $productName = $product->product_name ?? 'Selected product';
                    $toLocation = $transfer->toLocation->name ?? 'destination location';
                    $available = $toStock ? (int) $toStock->qty : 0;

                    throw new Exception(
                        "Cannot rollback {$productName}. {$toLocation} only has {$available} available, but rollback needs {$qty}."
                    );
                }

                $fromStock = ProductStock::query()
                    ->where('product_id', $item->product_id)
                    ->where('location_id', $transfer->from_location_id)
                    ->lockForUpdate()
                    ->first();

                if (! $fromStock) {
                    $fromStock = ProductStock::create([
                        'product_id' => $item->product_id,
                        'location_id' => $transfer->from_location_id,
                        'qty' => 0,
                    ]);
                }

                $toStock->decrement('qty', $qty);
                $fromStock->increment('qty', $qty);

                $item->update([
                    'status' => 'rolled_back',
                    'rolled_back_at' => now(),
                    'rolled_back_by' => $userId,
                ]);

                $product->update([
                    'stock_qty' => ProductStock::where('product_id', $item->product_id)->sum('qty'),
                ]);
            }

            $transfer->update([
                'status' => 'rolled_back',
                'rolled_back_at' => now(),
                'rolled_back_by' => $userId,
                'rollback_reason' => $reason,
            ]);

            return $transfer->fresh([
                'fromLocation',
                'toLocation',
                'items.product',
                'rollbackUser',
            ]);
        });
    }
}
