<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Enums\StockLevelStatus;
use App\Models\Location;
use App\Models\Product;
use App\Repositories\Contracts\Maintenance\LocationRepositoryInterface;
use App\Repositories\Contracts\Maintenance\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Maintenance Stock dashboard (Inventory → Maintenance Stock): stock per product at the
 * Main and Balintawak stockrooms, totals, and which products need a transfer.
 */
final class MaintenanceStockService
{
    public const LOCATION_FILTERS = ['main', 'balintawak', 'needs_transfer'];

    private const PER_PAGE = 10;

    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly LocationRepositoryInterface $locations,
    ) {}

    /**
     * @param  array{main: int, balintawak: int, transfer: int}  $pages
     * @param  array<string, mixed>  $query  the request query, kept on the page links
     * @return array{mainLocation: ?Location, balintawakLocation: ?Location, totals: array<string, int>, main: LengthAwarePaginator<int, Product>, balintawak: LengthAwarePaginator<int, Product>, transfer: LengthAwarePaginator<int, Product>}
     */
    public function dashboardData(string $search, ?string $locationFilter, array $pages, string $path, array $query): array
    {
        $locations = $this->locations->all();
        $main = $locations->first(fn (Location $location): bool => stripos((string) $location->name, 'main') !== false);
        $balintawak = $locations->first(fn (Location $location): bool => stripos((string) $location->name, 'balintawak') !== false);

        $products = $this->products->withStocks(trim($search))
            ->each(function (Product $product) use ($locations, $main, $balintawak): void {
                $perLocation = $locations->mapWithKeys(fn (Location $location): array => [
                    $location->id => (int) ($product->stocks->firstWhere('location_id', $location->id)->qty ?? 0),
                ]);
                $mainQty = $main ? $perLocation[$main->id] : 0;
                $balintawakQty = $balintawak ? $perLocation[$balintawak->id] : 0;
                $total = (int) $perLocation->sum();

                $product->setAttribute('main_qty', $mainQty);
                $product->setAttribute('balintawak_qty', $balintawakQty);
                $product->setAttribute('total_stock', $total);
                $product->setAttribute('stock_status', StockLevelStatus::fromQuantity($total)->value);
                $product->setAttribute('transfer_suggestion', $this->transferSuggestion($mainQty, $balintawakQty));
            });

        $products = match ($locationFilter) {
            'main' => $products->filter(fn (Product $product): bool => $product->main_qty > 0)->values(),
            'balintawak' => $products->filter(fn (Product $product): bool => $product->balintawak_qty > 0)->values(),
            'needs_transfer' => $products->filter(fn (Product $product): bool => filled($product->transfer_suggestion))->values(),
            default => $products,
        };

        return [
            'mainLocation' => $main,
            'balintawakLocation' => $balintawak,
            'totals' => [
                'items' => $products->count(),
                'stock' => (int) $products->sum('total_stock'),
                'main' => (int) $products->sum('main_qty'),
                'balintawak' => (int) $products->sum('balintawak_qty'),
                'low' => $products->filter(fn (Product $product): bool => $product->total_stock > 0 && $product->total_stock <= 5)->count(),
                'out' => $products->filter(fn (Product $product): bool => $product->total_stock <= 0)->count(),
            ],
            'main' => $this->paginate($products->filter(fn (Product $product): bool => $product->main_qty > 0)->sortByDesc('main_qty'), $pages['main'], 'main_page', $path, $query),
            'balintawak' => $this->paginate($products->filter(fn (Product $product): bool => $product->balintawak_qty > 0)->sortByDesc('balintawak_qty'), $pages['balintawak'], 'balintawak_page', $path, $query),
            'transfer' => $this->paginate($products->filter(fn (Product $product): bool => filled($product->transfer_suggestion)), $pages['transfer'], 'transfer_page', $path, $query),
        ];
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

    /**
     * Rows are re-indexed with values() so page 2+ stays a JSON list.
     *
     * @param  Collection<int, Product>  $items
     * @param  array<string, mixed>  $query
     * @return LengthAwarePaginator<int, Product>
     */
    private function paginate(Collection $items, int $page, string $pageName, string $path, array $query): LengthAwarePaginator
    {
        $page = max($page, 1);

        return new LengthAwarePaginator(
            $items->values()->forPage($page, self::PER_PAGE)->values(),
            $items->count(),
            self::PER_PAGE,
            $page,
            ['path' => $path, 'pageName' => $pageName, 'query' => $query]
        );
    }
}
