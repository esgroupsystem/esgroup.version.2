<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class ReceivingService
{
    public function create(array $data, ?UploadedFile $proof, ?int $userId): Receiving
    {
        $proofPath = null;

        try {
            return DB::transaction(function () use ($data, $proof, $userId, &$proofPath): Receiving {
                if ($proof !== null) {
                    $proofPath = $proof->store('receiving_proofs', 'local');
                }

                $receiving = Receiving::query()->create([
                    'receiving_number' => 'PENDING-'.Str::uuid(),
                    'location_id' => $data['location_id'],
                    'delivered_by' => $data['delivered_by'],
                    'delivery_date' => $data['delivery_date'],
                    'remarks' => $data['remarks'] ?? null,
                    'proof_image' => $proofPath,
                    'received_by' => $userId,
                ]);
                $receiving->update([
                    'receiving_number' => 'RCV-'.now()->format('Y').'-'.str_pad((string) $receiving->id, 5, '0', STR_PAD_LEFT),
                ]);

                foreach ($data['product_id'] as $index => $productId) {
                    $qty = (int) $data['qty_delivered'][$index];
                    $product = Product::query()->whereKey($productId)->lockForUpdate()->firstOrFail();
                    ReceivingItem::query()->create([
                        'receiving_id' => $receiving->id,
                        'product_id' => $product->id,
                        'qty_delivered' => $qty,
                        'qty_rolled_back' => 0,
                    ]);
                    $stock = $this->stockRowForUpdate((int) $product->id, (int) $data['location_id']);
                    $stock->increment('qty', $qty);
                    $this->syncProductTotalStock((int) $product->id);
                }

                return $receiving->fresh(['location', 'receiver', 'items.product']);
            }, 3);
        } catch (Throwable $e) {
            if ($proofPath && Storage::disk('local')->exists($proofPath)) {
                Storage::disk('local')->delete($proofPath);
            }
            throw $e;
        }
    }

    public function rollbackItem(int $receivingId, int $itemId, int $qty, ?int $userLocationId): string
    {
        return DB::transaction(function () use ($receivingId, $itemId, $qty, $userLocationId): string {
            $receiving = Receiving::query()->with('location')->whereKey($receivingId)->lockForUpdate()->firstOrFail();
            if ($userLocationId && (int) $receiving->location_id !== $userLocationId) {
                throw new RuntimeException('You are not allowed to rollback this receiving record.');
            }

            $item = ReceivingItem::query()->with('product')->where('receiving_id', $receiving->id)->whereKey($itemId)->lockForUpdate()->firstOrFail();
            if (! $item->product) {
                throw new RuntimeException('The selected product no longer exists.');
            }

            $name = $item->product->product_name ?? 'Selected product';
            $already = (int) ($item->qty_rolled_back ?? 0);
            $remaining = (int) $item->qty_delivered - $already;
            if ($remaining <= 0) {
                throw new RuntimeException("{$name} is already fully rolled back.");
            }
            if ($qty > $remaining) {
                throw new RuntimeException("Rollback quantity exceeds remaining quantity for {$name}. Remaining quantity: {$remaining}.");
            }

            $stock = ProductStock::query()->where('product_id', $item->product_id)->where('location_id', $receiving->location_id)->lockForUpdate()->first();
            $availableStock = $stock ? (int) $stock->qty : 0;
            if (! $stock || $availableStock < $qty) {
                throw new RuntimeException("Current stock is lower than rollback quantity for {$name}. Available stock: {$availableStock}.");
            }

            $stock->decrement('qty', $qty);
            $item->update(['qty_rolled_back' => $already + $qty, 'last_rolled_back_at' => now()]);
            $this->syncProductTotalStock((int) $item->product_id);

            return $name;
        }, 3);
    }

    private function stockRowForUpdate(int $productId, int $locationId): ProductStock
    {
        return ProductStock::query()->where('product_id', $productId)->where('location_id', $locationId)->lockForUpdate()->first()
            ?? ProductStock::query()->create(['product_id' => $productId, 'location_id' => $locationId, 'qty' => 0]);
    }

    private function syncProductTotalStock(int $productId): void
    {
        Product::query()->whereKey($productId)->update([
            'stock_qty' => ProductStock::query()->where('product_id', $productId)->sum('qty'),
        ]);
    }
}
