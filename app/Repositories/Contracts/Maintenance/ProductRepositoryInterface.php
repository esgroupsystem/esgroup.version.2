<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Maintenance;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Products (spare parts / supplies) and their total stock column. */
interface ProductRepositoryInterface
{
    /**
     * Product list: search on name, supplier, unit, part number, details and category. By name.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(string $search, string $pageName, int $perPage = 10): LengthAwarePaginator;

    /**
     * Product picker search (max 20), with the stock row of $locationId when given.
     *
     * @param  list<int>  $excludeIds
     * @return Collection<int, Product>
     */
    public function search(string $search, array $excludeIds, ?int $locationId): Collection;

    /** @return Collection<int, Product> every product (name / part number search) with stocks and their location */
    public function withStocks(string $search): Collection;

    public function findOrFail(int $id): Product;

    /** Locked product; fails when it does not exist. */
    public function findForUpdate(int $id): Product;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Product;

    /** @param array<string, mixed> $attributes */
    public function update(Product $product, array $attributes): void;

    /** Deletes the product and its stock rows. */
    public function delete(Product $product): void;
}
