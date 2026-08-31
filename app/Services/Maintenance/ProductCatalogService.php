<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Enums\StockLevelStatus;
use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class ProductCatalogService
{
    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh('category');
    }

    public function delete(Product $product): void
    {
        $product->stocks()->delete();
        $product->delete();
    }

    /** @return array{categories: Collection<int, Category>, items: LengthAwarePaginator, stock: LengthAwarePaginator, search: string} */
    public function indexData(string $search): array
    {
        $search = trim($search);

        $base = Product::query()
            ->with('category')
            ->when($search !== '', fn ($query) => $this->applySearch($query, $search))
            ->orderBy('product_name');

        return [
            'categories' => Category::query()->orderBy('name')->get(),
            'items' => (clone $base)->paginate(10, ['*'], 'items_page')->appends(['search' => $search]),
            'stock' => (clone $base)->paginate(10, ['*'], 'stock_page')->appends(['search' => $search]),
            'search' => $search,
        ];
    }

    /**
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     supplier_name: string|null,
     *     category: string|null,
     *     unit: string|null,
     *     part_number: string|null,
     *     details: string|null,
     *     stock: int
     * }>
     */
    public function searchProducts(
        string $search,
        array $excludeIds = [],
        ?int $locationId = null,
        bool $requireStock = false
    ): array {
        $search = trim($search);

        if ($search === '') {
            return [];
        }

        $products = Product::query()
            ->with([
                'category',
                'stocks' => function ($query) use ($locationId): void {
                    if ($locationId !== null) {
                        $query->where('location_id', $locationId);
                    }
                },
            ])
            ->when(
                $excludeIds !== [],
                fn ($query) => $query->whereNotIn('id', $excludeIds)
            )
            ->where(function ($query) use ($search): void {
                $query->where('product_name', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%");
            })
            ->orderBy('product_name')
            ->limit(20)
            ->get()
            ->map(function (Product $product) use ($locationId): array {
                $stockRow = $product->stocks->first();

                $stock = $locationId === null
                    ? (int) $product->stock_qty
                    : ($stockRow ? (int) $stockRow->qty : 0);

                return [
                    'id' => (int) $product->id,
                    'name' => (string) $product->product_name,
                    'supplier_name' => $product->supplier_name,
                    'category' => $product->category?->name,
                    'unit' => $product->unit,
                    'part_number' => $product->part_number,
                    'details' => $product->details,
                    'stock' => $stock,
                ];
            });

        if ($requireStock) {
            $products = $products->filter(
                fn (array $product): bool => $product['stock'] > 0
            );
        }

        /** @var array<int, array{
         *     id: int,
         *     name: string,
         *     supplier_name: string|null,
         *     category: string|null,
         *     unit: string|null,
         *     part_number: string|null,
         *     details: string|null,
         *     stock: int
         * }> $result
         */
        $result = $products
            ->values()
            ->all();

        return $result;
    }

    /** @return array<string, mixed> */
    public function dashboardData(string $search, ?string $locationFilter, int $mainPage, int $balintawakPage, int $transferPage, string $path, array $query): array
    {
        $locations = Location::query()->orderBy('name')->get();
        $mainLocation = $locations->first(fn (Location $location): bool => stripos((string) $location->name, 'main') !== false);
        $balintawakLocation = $locations->first(fn (Location $location): bool => stripos((string) $location->name, 'balintawak') !== false);

        $products = Product::query()
            ->with(['category', 'stocks.location'])
            ->when(trim($search) !== '', function ($builder) use ($search): void {
                $builder->where(function ($query) use ($search): void {
                    $query->where('product_name', 'like', "%{$search}%")
                        ->orWhere('part_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('product_name')
            ->get()
            ->map(function (Product $product) use ($locations, $mainLocation, $balintawakLocation): Product {
                $locationStocks = [];
                foreach ($locations as $location) {
                    $stockRow = $product->stocks->firstWhere('location_id', $location->id);
                    $locationStocks[$location->id] = $stockRow ? (int) $stockRow->qty : 0;
                }

                $mainQty = $mainLocation ? ($locationStocks[$mainLocation->id] ?? 0) : 0;
                $balintawakQty = $balintawakLocation ? ($locationStocks[$balintawakLocation->id] ?? 0) : 0;
                $totalQty = (int) collect($locationStocks)->sum();

                $product->setAttribute('location_stocks', $locationStocks);
                $product->setAttribute('main_qty', $mainQty);
                $product->setAttribute('balintawak_qty', $balintawakQty);
                $product->setAttribute('total_stock', $totalQty);
                $product->setAttribute('stock_status', StockLevelStatus::fromQuantity($totalQty)->value);
                $product->setAttribute('transfer_suggestion', $this->transferSuggestion($mainQty, $balintawakQty));

                return $product;
            });

        $products = match ($locationFilter) {
            'main' => $products->filter(fn (Product $product): bool => $product->main_qty > 0)->values(),
            'balintawak' => $products->filter(fn (Product $product): bool => $product->balintawak_qty > 0)->values(),
            'needs_transfer' => $products->filter(fn (Product $product): bool => filled($product->transfer_suggestion))->values(),
            default => $products,
        };

        $mainStocks = $products->filter(fn (Product $product): bool => $product->main_qty > 0)->sortByDesc('main_qty')->values();
        $balintawakStocks = $products->filter(fn (Product $product): bool => $product->balintawak_qty > 0)->sortByDesc('balintawak_qty')->values();
        $needsTransfer = $products->filter(fn (Product $product): bool => filled($product->transfer_suggestion))->values();

        return [
            'locations' => $locations,
            'mainLocation' => $mainLocation,
            'balintawakLocation' => $balintawakLocation,
            'products' => $products,
            'mainStocksPaginated' => $this->paginateCollection($mainStocks, $mainPage, 'main_page', $path, $query),
            'balintawakStocksPaginated' => $this->paginateCollection($balintawakStocks, $balintawakPage, 'balintawak_page', $path, $query),
            'needsTransferPaginated' => $this->paginateCollection($needsTransfer, $transferPage, 'transfer_page', $path, $query),
            'totalItems' => $products->count(),
            'totalStock' => $products->sum('total_stock'),
            'lowStock' => $products->filter(fn (Product $product): bool => $product->total_stock > 0 && $product->total_stock <= 5)->count(),
            'outOfStock' => $products->filter(fn (Product $product): bool => $product->total_stock <= 0)->count(),
            'mainTotalStock' => $products->sum('main_qty'),
            'balintawakTotalStock' => $products->sum('balintawak_qty'),
        ];
    }

    private function applySearch($query, string $search): void
    {
        $query->where(function ($query) use ($search): void {
            $query->where('product_name', 'like', "%{$search}%")
                ->orWhere('supplier_name', 'like', "%{$search}%")
                ->orWhere('unit', 'like', "%{$search}%")
                ->orWhere('part_number', 'like', "%{$search}%")
                ->orWhere('details', 'like', "%{$search}%")
                ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
        });
    }

    private function transferSuggestion(int $mainQty, int $balintawakQty): ?string
    {
        return match (true) {
            $mainQty > 0 && $balintawakQty <= 0 => 'Available in Main but zero in Balintawak',
            $balintawakQty > 0 && $mainQty <= 0 => 'Available in Balintawak but zero in Main',
            $mainQty >= 10 && $balintawakQty <= 2 => 'Needs transfer to Balintawak',
            $balintawakQty >= 10 && $mainQty <= 2 => 'Needs transfer to Main',
            default => null,
        };
    }

    private function paginateCollection(Collection $items, int $page, string $pageName, string $path, array $query): LengthAwarePaginator
    {
        $perPage = 10;

        return new LengthAwarePaginator(
            $items->forPage(max($page, 1), $perPage),
            $items->count(),
            $perPage,
            max($page, 1),
            ['path' => $path, 'pageName' => $pageName, 'query' => $query]
        );
    }
}
