<?php

declare(strict_types=1);

namespace App\Repositories\Maintenance;

use App\Models\Product;
use App\Repositories\Contracts\Maintenance\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

final class ProductRepository implements ProductRepositoryInterface
{
    private const SEARCH_COLUMNS = ['product_name', 'supplier_name', 'unit', 'part_number', 'details'];

    public function paginate(string $search, string $pageName, int $perPage = 10): LengthAwarePaginator
    {
        return Product::query()
            ->with('category')
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $inner) use ($search): void {
                $this->matchColumns($inner, $search);
                $inner->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', "%{$search}%"));
            }))
            ->orderBy('product_name')
            ->paginate($perPage, ['*'], $pageName)
            ->appends(['search' => $search]);
    }

    public function search(string $search, array $excludeIds, ?int $locationId): Collection
    {
        return Product::query()
            ->with(['category', 'stocks' => fn (HasMany $query) => $query->when($locationId !== null, fn ($stocks) => $stocks->where('location_id', $locationId))])
            ->when($excludeIds !== [], fn (Builder $query) => $query->whereNotIn('id', $excludeIds))
            ->where(fn (Builder $inner) => $this->matchColumns($inner, $search))
            ->orderBy('product_name')
            ->limit(20)
            ->get();
    }

    public function withStocks(string $search): Collection
    {
        return Product::query()
            ->with(['category', 'stocks.location'])
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('product_name', 'like', "%{$search}%")
                ->orWhere('part_number', 'like', "%{$search}%")))
            ->orderBy('product_name')
            ->get();
    }

    public function findOrFail(int $id): Product
    {
        return Product::query()->findOrFail($id);
    }

    public function findForUpdate(int $id): Product
    {
        return Product::query()->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function create(array $attributes): Product
    {
        return Product::query()->create($attributes);
    }

    public function update(Product $product, array $attributes): void
    {
        $product->update($attributes);
    }

    public function delete(Product $product): void
    {
        $product->stocks()->delete();
        $product->delete();
    }

    /** @param Builder<Product> $query */
    private function matchColumns(Builder $query, string $search): void
    {
        foreach (self::SEARCH_COLUMNS as $column) {
            $query->orWhere($column, 'like', "%{$search}%");
        }
    }
}
