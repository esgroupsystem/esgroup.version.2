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
use Illuminate\Http\Response;
use Illuminate\View\View;

final class ItemsController extends Controller
{
    public function __construct(private readonly ProductCatalogService $productCatalogService) {}

    public function index(Request $request): View|string|Response
    {
        $target = trim((string) $request->input('target', ''));
        $data = $this->productCatalogService->indexData((string) $request->input('search', ''));

        if ($request->ajax()) {
            if ($target === 'items') {
                return view('maintenance.items.items_table', ['items' => $data['items']])->render();
            }

            if ($target === 'stock') {
                return view('maintenance.items.stock_table', ['products' => $data['stock']])->render();
            }

            return response('<div class="alert alert-danger m-3">Invalid AJAX target.</div>', 400);
        }

        return view('maintenance.items.index', $data);
    }

    public function dashboard(Request $request): View|RedirectResponse
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

        return view('maintenance.items.dashboard', [
            'search' => $search,
            'locationFilter' => $locationFilter,
            ...$data,
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
