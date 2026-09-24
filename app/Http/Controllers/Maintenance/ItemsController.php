<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\ProductRequest;
use App\Models\Product;
use App\Services\Maintenance\ProductCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

final class ItemsController extends Controller
{
    public function __construct(private readonly ProductCatalogService $productCatalogService) {}

    public function index(Request $request): InertiaResponse
    {
        $data = $this->productCatalogService->indexData((string) $request->input('search', ''));
        $user = $request->user();
        $row = fn (Product $product): array => [
            'id' => $product->id,
            'category_id' => (string) $product->category_id,
            'category' => $product->category?->name,
            'product_name' => $product->product_name,
            'supplier_name' => $product->supplier_name,
            'unit' => $product->unit,
            'part_number' => $product->part_number,
            'details' => $product->details,
            'stock_qty' => (int) ($product->stock_qty ?? 0),
            'update_url' => route('items.update', $product->id),
            'destroy_url' => route('items.destroy', $product->id),
        ];

        return Inertia::render('products/items/index', [
            'items' => $data['items']->through($row),
            'stock' => $data['stock']->through($row),
            'categories' => $data['categories']->map(fn ($category): array => ['value' => (string) $category->id, 'label' => $category->name])->values(),
            'filters' => ['search' => $data['search'], 'showStock' => $request->boolean('stock')],
            'can' => [
                'create' => (bool) $user?->can('items.create'),
                'update' => (bool) $user?->can('items.update'),
                'delete' => (bool) $user?->can('items.delete'),
            ],
            'urls' => ['index' => route('items.index'), 'store' => route('items.store')],
        ]);
    }

    public function dashboard(Request $request): InertiaResponse|RedirectResponse
    {
        $locationFilter = $request->input('location');
        if (! in_array($locationFilter, ['main', 'balintawak', 'needs_transfer', null, ''], true)) {
            flash('Invalid location filter selected.')->error();

            return back();
        }

        $search = trim((string) $request->input('search', ''));
        $data = $this->productCatalogService->dashboardData(
            $search,
            is_string($locationFilter) ? $locationFilter : null,
            max((int) $request->input('main_page', 1), 1),
            max((int) $request->input('balintawak_page', 1), 1),
            max((int) $request->input('transfer_page', 1), 1),
            $request->url(),
            $request->query()
        );

        $tab = (string) $request->input('tab', '');

        return Inertia::render('dashboards/maintenance-stock/index', [
            'filters' => [
                'search' => $search,
                'location' => (string) ($locationFilter ?? ''),
                'tab' => in_array($tab, ['main', 'balintawak', 'transfer'], true) ? $tab : 'main',
            ],
            'locationNames' => [
                'main' => $data['mainLocation']->name ?? 'Main',
                'balintawak' => $data['balintawakLocation']->name ?? 'Balintawak',
            ],
            'totals' => [
                'items' => $data['totalItems'],
                'stock' => (int) $data['totalStock'],
                'main' => (int) $data['mainTotalStock'],
                'balintawak' => (int) $data['balintawakTotalStock'],
                'low' => $data['lowStock'],
                'out' => $data['outOfStock'],
            ],
            'main' => $this->stockPage($data['mainStocksPaginated'], 'main_qty'),
            'balintawak' => $this->stockPage($data['balintawakStocksPaginated'], 'balintawak_qty'),
            'transfer' => $this->stockPage($data['needsTransferPaginated'], 'main_qty'),
            'urls' => ['index' => route('items.dashboard'), 'items' => route('items.index')],
        ]);
    }

    /**
     * Paginator → Inertia payload. The service paginates a collection with
     * forPage(), which keeps keys, so rows are re-indexed to stay a JSON list.
     *
     * @return array<string, mixed>
     */
    private function stockPage(LengthAwarePaginator $paginator, string $qtyField): array
    {
        $page = $paginator->toArray();
        $page['data'] = collect($paginator->items())->map(fn (Product $product): array => [
            'id' => $product->id,
            'category' => $product->category->name ?? ($product->category->category_name ?? '—'),
            'name' => $product->product_name,
            'details' => $product->details,
            'part_number' => $product->part_number,
            'unit' => $product->unit,
            'qty' => (int) $product->{$qtyField},
            'main_qty' => (int) $product->main_qty,
            'balintawak_qty' => (int) $product->balintawak_qty,
            'suggestion' => $product->transfer_suggestion,
        ])->values()->all();

        return $page;
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->productCatalogService->create($request->validated());
        flash('Item added successfully!')->success();

        return back();
    }

    public function update(ProductRequest $request, int $id): RedirectResponse
    {
        $this->productCatalogService->update(Product::query()->findOrFail($id), $request->validated());
        flash('Item updated successfully!')->success();

        return back();
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()?->can('items.delete'), 403);
        $this->productCatalogService->delete(Product::query()->findOrFail($id));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Item deleted successfully']);
        }

        return back()->with('success', 'Item deleted successfully');
    }
}
