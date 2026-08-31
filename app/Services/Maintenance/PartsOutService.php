<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Enums\InventoryTransactionStatus;
use App\Models\Location;
use App\Models\PartsOut;
use App\Models\PartsOutItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class PartsOutService
{
    public function create(array $data, ?int $userId): PartsOut
    {
        return DB::transaction(function () use ($data, $userId): PartsOut {
            $location = Location::query()->whereKey($data['location_id'])->lockForUpdate()->firstOrFail();

            $partsOut = PartsOut::query()->create([
                'parts_out_number' => 'TEMP',
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'location_id' => $data['location_id'],
                'mechanic_name' => $data['mechanic_name'],
                'requested_by' => $data['requested_by'] ?? null,
                'issued_date' => $data['issued_date'],
                'job_order_no' => $data['job_order_no'] ?? null,
                'odometer' => $data['odometer'] ?? null,
                'purpose' => $data['purpose'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => InventoryTransactionStatus::Posted->value,
                'created_by' => $userId,
            ]);

            $number = 'POUT-'.now()->format('Y').'-'.str_pad((string) $partsOut->id, 5, '0', STR_PAD_LEFT);
            $partsOut->update(['parts_out_number' => $number]);

            foreach ($data['product_id'] as $index => $productId) {
                $qty = (int) $data['qty_used'][$index];
                $product = Product::query()->whereKey($productId)->lockForUpdate()->firstOrFail();
                $stock = ProductStock::query()
                    ->where('product_id', $product->id)
                    ->where('location_id', $location->id)
                    ->lockForUpdate()
                    ->first();

                if (! $stock) {
                    throw new RuntimeException("No stock record found for {$product->product_name} at {$location->name}.");
                }

                $before = (int) $stock->qty;
                if ($before < $qty) {
                    throw new RuntimeException("Insufficient stock for {$product->product_name} at {$location->name}. Available: {$before}, Requested: {$qty}.");
                }

                $after = $before - $qty;
                PartsOutItem::query()->create([
                    'parts_out_id' => $partsOut->id,
                    'product_id' => $product->id,
                    'qty_used' => $qty,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'remarks' => $data['item_remarks'][$index] ?? null,
                ]);
                $stock->update(['qty' => $after]);
                $this->syncProductTotalStock((int) $product->id);

                StockMovement::query()->create([
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'reference_type' => 'parts_out',
                    'reference_id' => $partsOut->id,
                    'movement_type' => 'out',
                    'qty' => $qty,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'transaction_date' => $data['issued_date'],
                    'remarks' => 'Parts Out #'.$number,
                    'created_by' => $userId,
                ]);
            }

            return $partsOut->fresh(['vehicle', 'location', 'items.product']);
        }, 3);
    }

    private function syncProductTotalStock(int $productId): void
    {
        Product::query()->whereKey($productId)->update([
            'stock_qty' => ProductStock::query()->where('product_id', $productId)->sum('qty'),
        ]);
    }
}
