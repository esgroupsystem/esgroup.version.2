<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Enums\InventoryTransactionStatus;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class StockTransferCreationService
{
    public function create(array $data, ?int $userId): StockTransfer
    {
        return DB::transaction(function () use ($data, $userId): StockTransfer {
            $from = Location::query()->whereKey($data['from_location_id'])->where('is_active', true)->lockForUpdate()->first();
            $to = Location::query()->whereKey($data['to_location_id'])->where('is_active', true)->lockForUpdate()->first();
            if (! $from) {
                throw new RuntimeException('Source location is inactive or not found.');
            }
            if (! $to) {
                throw new RuntimeException('Destination location is inactive or not found.');
            }

            $transfer = StockTransfer::query()->create([
                'transfer_number' => 'TEMP',
                'from_location_id' => $from->id,
                'to_location_id' => $to->id,
                'transfer_date' => $data['transfer_date'],
                'requested_by' => $data['requested_by'] ?? null,
                'received_by' => $data['received_by'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => InventoryTransactionStatus::Completed->value,
                'created_by' => $userId,
            ]);
            $transfer->update([
                'transfer_number' => 'ST-'.now()->format('Y').'-'.str_pad((string) $transfer->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($data['product_id'] as $index => $productId) {
                $qty = (int) $data['qty'][$index];
                $product = Product::query()->whereKey($productId)->lockForUpdate()->firstOrFail();
                $fromStock = ProductStock::query()->where('product_id', $product->id)->where('location_id', $from->id)->lockForUpdate()->first();
                $availableStock = $fromStock ? (int) $fromStock->qty : 0;
                if (! $fromStock || $availableStock < $qty) {
                    throw new RuntimeException("Insufficient stock for product: {$product->product_name}. Available in {$from->name}: {$availableStock}, Requested: {$qty}.");
                }

                $toStock = ProductStock::query()->where('product_id', $product->id)->where('location_id', $to->id)->lockForUpdate()->first()
                    ?? ProductStock::query()->create(['product_id' => $product->id, 'location_id' => $to->id, 'qty' => 0]);

                $fromStock->decrement('qty', $qty);
                $toStock->increment('qty', $qty);
                StockTransferItem::query()->create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'status' => InventoryTransactionStatus::Completed->value,
                ]);
                $product->update(['stock_qty' => ProductStock::query()->where('product_id', $product->id)->sum('qty')]);
            }

            return $transfer->fresh(['fromLocation', 'toLocation', 'items.product']);
        }, 3);
    }
}
