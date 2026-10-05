<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\ProductRequest;
use App\Http\Resources\Maintenance\ProductResource;
use App\Http\Resources\Maintenance\StockLevelResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\Maintenance\MaintenanceStockService;
use App\Services\Maintenance\ProductCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/** Products → Products (items.*) and Inventory → Maintenance Stock (items.dashboard). */
final class ItemsController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $productCatalogService,
        private readonly MaintenanceStockService $maintenanceStockService,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $data = $this->productCatalogService->indexData((string) $request->input('search', ''));
        $user = $request->user();
        $row = fn (Product $product): array => ProductResource::make($product)->resolve($request);

        return Inertia::render('products/items/index', [
            'items' => $data['items']->through($row),
            'stock' => $data['stock']->through($row),
            'categories' => $data['categories']->map(fn (Category $category): array => ['value' => (string) $category->id, 'label' => $category->name])->values(),
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
        if (! in_array($locationFilter, [...MaintenanceStockService::LOCATION_FILTERS, null, ''], true)) {
            flash('Invalid location filter selected.')->error();

            return back();
        }

        $search = trim((string) $request->input('search', ''));
        $data = $this->maintenanceStockService->dashboardData(
            $search,
            is_string($locationFilter) ? $locationFilter : null,
            [
                'main' => (int) $request->input('main_page', 1),
                'balintawak' => (int) $request->input('balintawak_page', 1),
                'transfer' => (int) $request->input('transfer_page', 1),
            ],
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
            'totals' => $data['totals'],
            'main' => $this->stockPage($request, $data['main'], 'main_qty'),
            'balintawak' => $this->stockPage($request, $data['balintawak'], 'balintawak_qty'),
            'transfer' => $this->stockPage($request, $data['transfer'], 'main_qty'),
            'urls' => ['index' => route('items.dashboard'), 'items' => route('items.index')],
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->productCatalogService->create($request->validated());
        flash('Item added successfully!')->success();

        return back();
    }

    public function update(ProductRequest $request, int $id): RedirectResponse
    {
        $this->productCatalogService->update($id, $request->validated());
        flash('Item updated successfully!')->success();

        return back();
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        abort_unless($request->user()?->can('items.delete'), 403);
        $this->productCatalogService->delete($id);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Item deleted successfully']);
        }

        return back()->with('success', 'Item deleted successfully');
    }

    /**
     * @param  LengthAwarePaginator<int, Product>  $paginator
     * @return array<string, mixed>
     */
    private function stockPage(Request $request, LengthAwarePaginator $paginator, string $qtyField): array
    {
        $page = $paginator->toArray();
        $page['data'] = collect($paginator->items())
            ->map(fn (Product $product): array => (new StockLevelResource($product, $qtyField))->resolve($request))
            ->values()
            ->all();

        return $page;
    }
}
