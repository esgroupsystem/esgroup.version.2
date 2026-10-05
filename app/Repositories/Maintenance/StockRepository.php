<?php

declare(strict_types=1);

namespace App\Repositories\Maintenance;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Repositories\Contracts\Maintenance\StockRepositoryInterface;
use Illuminate\Support\Collection;

final class StockRepository implements StockRepositoryInterface
{
    public function lockRow(int $productId, int $locationId): ?ProductStock
    {
        return ProductStock::query()->where('product_id', $productId)->where('location_id', $locationId)->lockForUpdate()->first();
    }

    public function lockOrCreateRow(int $productId, int $locationId): ProductStock
    {
        return $this->lockRow($productId, $locationId)
            ?? ProductStock::query()->create(['product_id' => $productId, 'location_id' => $locationId, 'qty' => 0]);
    }

    public function setQuantity(ProductStock $stock, int $quantity): void
    {
        $stock->update(['qty' => $quantity]);
    }

    public function add(ProductStock $stock, int $quantity): void
    {
        $stock->increment('qty', $quantity);
    }

    public function remove(ProductStock $stock, int $quantity): void
    {
        $stock->decrement('qty', $quantity);
    }

    public function syncProductTotal(int $productId): void
    {
        Product::query()->whereKey($productId)->update([
            'stock_qty' => ProductStock::query()->where('product_id', $productId)->sum('qty'),
        ]);
    }

    public function recordMovement(array $attributes): StockMovement
    {
        return StockMovement::query()->create($attributes);
    }

    public function quantities(int $locationId, Collection $productIds): Collection
    {
        return ProductStock::query()
            ->where('location_id', $locationId)
            ->whereIn('product_id', $productIds)
            ->pluck('qty', 'product_id')
            ->map(fn ($qty): int => (int) $qty);
    }
}
