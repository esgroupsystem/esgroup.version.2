<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\RollbackInventoryRequest;
use App\Http\Requests\Maintenance\StoreStockTransferRequest;
use App\Models\Location;
use App\Models\StockTransfer;
use App\Services\Maintenance\InventoryDirectoryService;
use App\Services\Maintenance\ProductCatalogService;
use App\Services\Maintenance\StockTransferCreationService;
use App\Services\Maintenance\StockTransferRollbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class StockTransferController extends Controller
{
    public function __construct(
        private readonly InventoryDirectoryService $directoryService,
        private readonly ProductCatalogService $productCatalogService,
        private readonly StockTransferCreationService $creationService,
        private readonly StockTransferRollbackService $rollbackService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $transfers = $this->directoryService->stockTransfers($search);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('maintenance.stock_transfers.table', compact('transfers'))->render(),
            ]);
        }

        return view('maintenance.stock_transfers.index', compact('transfers', 'search'));
    }

    public function create(): View
    {
        return view('maintenance.stock_transfers.create', [
            'locations' => $this->directoryService->activeLocations(),
        ]);
    }

    public function store(StoreStockTransferRequest $request): RedirectResponse
    {
        try {
            $transfer = $this->creationService->create($request->validated(), $request->user()?->id);

            return redirect()->route('stock-transfers.show', $transfer)->with('success', 'Stock transfer created successfully.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(StockTransfer $stock_transfer): View
    {
        $stock_transfer->load(['fromLocation', 'toLocation', 'creator', 'rollbackUser', 'items.product.category', 'items.rollbackUser']);

        return view('maintenance.stock_transfers.show', ['transfer' => $stock_transfer]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('q', ''));
        $locationId = (int) $request->input('from_location_id');
        if (mb_strlen($search) < 2 || ! $locationId) {
            return response()->json([]);
        }

        $locationExists = Location::query()->whereKey($locationId)->where('is_active', true)->exists();
        if (! $locationExists) {
            return response()->json(['success' => false, 'message' => 'Selected source location is invalid or inactive.'], 422);
        }

        $excludeIds = collect($request->input('exclude_ids', []))
            ->flatten()->filter(fn ($id): bool => is_numeric($id))->map(fn ($id): int => (int) $id)->unique()->values()->all();

        return response()->json($this->productCatalogService->searchProducts($search, $excludeIds, $locationId, true));
    }

    public function rollback(RollbackInventoryRequest $request, StockTransfer $stock_transfer): RedirectResponse
    {
        abort_unless($request->user()?->can('stock-transfers.rollback'), 403);

        try {
            $this->rollbackService->rollback($stock_transfer, (int) $request->user()->id, $request->validated('rollback_reason'));

            return redirect()->route('stock-transfers.show', $stock_transfer)->with('success', 'Stock transfer rolled back successfully. Item quantities were returned to the original location.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
