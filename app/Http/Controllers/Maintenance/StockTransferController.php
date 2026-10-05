<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\RollbackInventoryRequest;
use App\Http\Requests\Maintenance\StoreStockTransferRequest;
use App\Http\Resources\Maintenance\StockTransferDetailResource;
use App\Http\Resources\Maintenance\StockTransferRowResource;
use App\Models\StockTransfer;
use App\Services\Maintenance\InventoryDirectoryService;
use App\Services\Maintenance\ProductCatalogService;
use App\Services\Maintenance\StockTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Inventory → Stock Transfer. */
final class StockTransferController extends Controller
{
    public function __construct(
        private readonly InventoryDirectoryService $directory,
        private readonly ProductCatalogService $productCatalogService,
        private readonly StockTransferService $transferService,
    ) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $user = $request->user();

        return Inertia::render('inventory/stock-transfers/index', [
            'records' => $this->transferService->paginate($search)
                ->through(fn (StockTransfer $transfer): array => StockTransferRowResource::make($transfer)->resolve($request)),
            'filters' => ['search' => $search],
            'can' => [
                'create' => (bool) $user?->can('stock-transfers.create'),
                'rollback' => (bool) $user?->can('stock-transfers.rollback'),
            ],
            'urls' => ['index' => route('stock-transfers.index'), 'create' => route('stock-transfers.create')],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('inventory/stock-transfers/create', [
            'locations' => $this->directory->locationOptions(),
            'today' => now()->format('Y-m-d'),
            'urls' => [
                'index' => route('stock-transfers.index'),
                'store' => route('stock-transfers.store'),
                'search' => route('stock-transfers.search-products'),
            ],
        ]);
    }

    public function store(StoreStockTransferRequest $request): RedirectResponse
    {
        $transfer = $this->transferService->create($request->validated(), $request->user()?->id);

        return redirect()->route('stock-transfers.show', $transfer)->with('success', 'Stock transfer created successfully.');
    }

    public function show(Request $request, StockTransfer $stock_transfer): Response
    {
        $transfer = $this->transferService->loadForShow($stock_transfer);
        $user = $request->user();

        return Inertia::render('inventory/stock-transfers/show', [
            'record' => StockTransferDetailResource::make($transfer)->resolve($request),
            'can' => [
                'create' => (bool) $user?->can('stock-transfers.create'),
                'rollback' => ! StockTransferDetailResource::isRolledBack($transfer) && (bool) $user?->can('stock-transfers.rollback'),
            ],
            'urls' => [
                'index' => route('stock-transfers.index'),
                'create' => route('stock-transfers.create'),
                'rollback' => route('stock-transfers.rollback', $transfer->id),
            ],
        ]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('q', ''));
        $locationId = (int) $request->input('from_location_id');
        if (mb_strlen($search) < 2 || ! $locationId) {
            return response()->json([]);
        }

        if (! $this->transferService->isActiveLocation($locationId)) {
            return response()->json(['success' => false, 'message' => 'Selected source location is invalid or inactive.'], 422);
        }

        return response()->json($this->productCatalogService->searchProducts($search, $this->directory->excludeIds($request), $locationId, true));
    }

    public function rollback(RollbackInventoryRequest $request, StockTransfer $stock_transfer): RedirectResponse
    {
        abort_unless($request->user()?->can('stock-transfers.rollback'), 403);

        $this->transferService->rollback($stock_transfer->id, (int) $request->user()->id, $request->validated('rollback_reason'));

        return redirect()->route('stock-transfers.show', $stock_transfer)->with('success', 'Stock transfer rolled back successfully. Item quantities were returned to the original location.');
    }
}
