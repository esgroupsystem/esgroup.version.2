<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\Maintenance\CategoryRepositoryInterface;
use App\Repositories\Contracts\Maintenance\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Products: the item list, create / edit / delete, and the product search used by the inventory forms. */
final class ProductCatalogService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(array $data): Product
    {
        return $this->products->create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): Product
    {
        $product = $this->products->findOrFail($id);
        $this->products->update($product, $data);

        return $product;
    }

    /** Deletes the product and its stock rows. */
    public function delete(int $id): void
    {
        $this->products->delete($this->products->findOrFail($id));
    }

    /**
     * The Items page: the item list and the stock list are two paginators over the same search.
     *
     * @return array{categories: Collection<int, Category>, items: LengthAwarePaginator<int, Product>, stock: LengthAwarePaginator<int, Product>, search: string}
     */
    public function indexData(string $search): array
    {
        $search = trim($search);

        return [
            'categories' => $this->categories->all(),
            'items' => $this->products->paginate($search, 'items_page'),
            'stock' => $this->products->paginate($search, 'stock_page'),
            'search' => $search,
        ];
    }

    /**
     * Up to 20 products matching the search, for the inventory form pickers. `stock` is the
     * quantity at $locationId, or the product total when no location is given.
     *
     * @param  list<int>  $excludeIds
     * @return list<array{id: int, name: string, supplier_name: string|null, category: string|null, unit: string|null, part_number: string|null, details: string|null, stock: int}>
     */
    public function searchProducts(string $search, array $excludeIds = [], ?int $locationId = null, bool $requireStock = false): array
    {
        $search = trim($search);

        if ($search === '') {
            return [];
        }

        return $this->products->search($search, $excludeIds, $locationId)
            ->map(fn (Product $product): array => [
                'id' => (int) $product->id,
                'name' => (string) $product->product_name,
                'supplier_name' => $product->supplier_name,
                'category' => $product->category?->name,
                'unit' => $product->unit,
                'part_number' => $product->part_number,
                'details' => $product->details,
                'stock' => $locationId === null ? (int) $product->stock_qty : (int) ($product->stocks->first()->qty ?? 0),
            ])
            ->when($requireStock, fn (Collection $rows) => $rows->filter(fn (array $row): bool => $row['stock'] > 0))
            ->values()
            ->all();
    }
}
