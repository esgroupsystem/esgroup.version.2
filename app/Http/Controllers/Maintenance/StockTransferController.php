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
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class StockTransferController extends Controller
{
    public function __construct(
        private readonly InventoryDirectoryService $directoryService,
        private readonly ProductCatalogService $productCatalogService,
        private readonly StockTransferCreationService $creationService,
        private readonly StockTransferRollbackService $rollbackService,
    ) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $user = $request->user();
        $canRollback = (bool) $user?->can('stock-transfers.rollback');

        return Inertia::render('inventory/stock-transfers/index', [
            'records' => $this->directoryService->stockTransfers($search)->through(fn (StockTransfer $transfer): array => [
                'id' => $transfer->id,
                'number' => $transfer->transfer_number,
                'creator' => $transfer->creator->full_name ?? ($transfer->creator->name ?? 'System'),
                'rolled_back' => ($transfer->status ?? 'completed') === 'rolled_back',
                'rolled_back_at' => $transfer->rolled_back_at?->format('M d, Y h:i A'),
                'from' => $transfer->fromLocation->name ?? 'N/A',
                'to' => $transfer->toLocation->name ?? 'N/A',
                'requested_by' => $transfer->requested_by ?: '—',
                'received_by' => $transfer->received_by ?: '—',
                'items_count' => (int) ($transfer->items_count ?? 0),
                'remarks' => $transfer->remarks,
                'created_date' => $transfer->created_at?->format('M d, Y'),
                'created_time' => $transfer->created_at?->format('h:i A'),
                'show_url' => route('stock-transfers.show', $transfer->id),
                'rollback_url' => route('stock-transfers.rollback', $transfer->id),
            ]),
            'filters' => ['search' => $search],
            'can' => ['create' => (bool) $user?->can('stock-transfers.create'), 'rollback' => $canRollback],
            'urls' => ['index' => route('stock-transfers.index'), 'create' => route('stock-transfers.create')],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('inventory/stock-transfers/create', [
            'locations' => $this->directoryService->activeLocations()->map(fn ($location): array => ['value' => (string) $location->id, 'label' => (string) $location->name])->values(),
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
        try {
            $transfer = $this->creationService->create($request->validated(), $request->user()?->id);

            return redirect()->route('stock-transfers.show', $transfer)->with('success', 'Stock transfer created successfully.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Request $request, StockTransfer $stock_transfer): Response
    {
        $stock_transfer->load(['fromLocation', 'toLocation', 'creator', 'rollbackUser', 'items.product.category', 'items.rollbackUser']);
        $rolledBack = ($stock_transfer->status ?? 'completed') === 'rolled_back';

        return Inertia::render('inventory/stock-transfers/show', [
            'record' => [
                'id' => $stock_transfer->id,
                'number' => $stock_transfer->transfer_number,
                'date' => $stock_transfer->transfer_date?->format('F d, Y') ?? '—',
                'from' => $stock_transfer->fromLocation->name ?? 'N/A',
                'to' => $stock_transfer->toLocation->name ?? 'N/A',
                'requested_by' => $stock_transfer->requested_by ?? '—',
                'received_by' => $stock_transfer->received_by ?? '—',
                'creator' => $stock_transfer->creator->full_name ?? ($stock_transfer->creator->name ?? '—'),
                'remarks' => $stock_transfer->remarks,
                'rolled_back' => $rolledBack,
                'rollback' => $rolledBack ? [
                    'by' => $stock_transfer->rollbackUser->full_name ?? ($stock_transfer->rollbackUser->name ?? 'System'),
                    'at' => $stock_transfer->rolled_back_at?->format('F d, Y h:i A'),
                    'reason' => $stock_transfer->rollback_reason,
                ] : null,
                'items' => $stock_transfer->items->values()->map(fn ($item): array => [
                    'id' => $item->id,
                    'name' => $item->product->product_name ?? 'N/A',
                    'category' => $item->product?->category?->name,
                    'part_number' => $item->product->part_number ?? '—',
                    'unit' => $item->product->unit ?? '—',
                    'qty' => (int) $item->qty,
                    'rolled_back' => ($item->status ?? 'completed') === 'rolled_back',
                ]),
            ],
            'can' => [
                'create' => (bool) $request->user()?->can('stock-transfers.create'),
                'rollback' => ! $rolledBack && (bool) $request->user()?->can('stock-transfers.rollback'),
            ],
            'urls' => [
                'index' => route('stock-transfers.index'),
                'create' => route('stock-transfers.create'),
                'rollback' => route('stock-transfers.rollback', $stock_transfer->id),
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
