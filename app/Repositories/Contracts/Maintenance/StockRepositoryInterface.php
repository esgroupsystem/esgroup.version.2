<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Maintenance;

use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Support\Collection;

/**
 * Per-location stock rows (product_stocks), the stock ledger (stock_movements) and the product's
 * total stock_qty. Every stock change in Parts Issuance, Receiving and Stock Transfer goes through here.
 */
interface StockRepositoryInterface
{
    /** The locked stock row, or null when the product was never stocked there. */
    public function lockRow(int $productId, int $locationId): ?ProductStock;

    /** The locked stock row, created with 0 when missing. */
    public function lockOrCreateRow(int $productId, int $locationId): ProductStock;

    public function setQuantity(ProductStock $stock, int $quantity): void;

    public function add(ProductStock $stock, int $quantity): void;

    public function remove(ProductStock $stock, int $quantity): void;

    /** Recomputes products.stock_qty as the sum of the product's stock rows. */
    public function syncProductTotal(int $productId): void;

    /** @param array<string, mixed> $attributes */
    public function recordMovement(array $attributes): StockMovement;

    /**
     * @param  Collection<int, int>  $productIds
     * @return Collection<int, int> product id => quantity at the location
     */
    public function quantities(int $locationId, Collection $productIds): Collection;
}
