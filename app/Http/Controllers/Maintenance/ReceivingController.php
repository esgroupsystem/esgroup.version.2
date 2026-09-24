<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\RollbackReceivingItemRequest;
use App\Http\Requests\Maintenance\StoreReceivingRequest;
use App\Models\ProductStock;
use App\Models\Receiving;
use App\Services\Maintenance\InventoryDirectoryService;
use App\Services\Maintenance\ProductCatalogService;
use App\Services\Maintenance\ReceivingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class ReceivingController extends Controller
{
    public function __construct(
        private readonly InventoryDirectoryService $directoryService,
        private readonly ProductCatalogService $productCatalogService,
        private readonly ReceivingService $receivingService,
    ) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        return Inertia::render('inventory/receivings/index', [
            'records' => $this->directoryService->receivings($search, $this->userLocationId($request))
                ->through(fn (Receiving $receiving): array => [
                    'id' => $receiving->id,
                    'number' => $receiving->receiving_number,
                    'location' => $receiving->location->name ?? 'N/A',
                    'delivered_by' => $receiving->delivered_by ?: 'Not specified',
                    'delivery_date' => $receiving->delivery_date ? Carbon::parse($receiving->delivery_date)->format('M d, Y') : null,
                    'delivery_day' => $receiving->delivery_date ? Carbon::parse($receiving->delivery_date)->format('l') : null,
                    'items_count' => (int) ($receiving->items_count ?? 0),
                    'remarks' => $receiving->remarks,
                    'receiver' => $receiving->receiver->full_name ?? ($receiving->receiver->name ?? 'System'),
                    'created_date' => $receiving->created_at?->format('M d, Y'),
                    'created_time' => $receiving->created_at?->format('h:i A'),
                    'show_url' => route('receivings.show', $receiving->id),
                ]),
            'filters' => ['search' => $search],
            'can' => ['create' => (bool) $request->user()?->can('receivings.create')],
            'urls' => [
                'index' => route('receivings.index'),
                'create' => route('receivings.create'),
                'dashboard' => route('items.dashboard'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $locations = $this->directoryService->activeLocations($this->userLocationId($request));

        return Inertia::render('inventory/receivings/create', [
            'locations' => $locations->map(fn ($location): array => ['value' => (string) $location->id, 'label' => (string) $location->name])->values(),
            'today' => now()->format('Y-m-d'),
            'urls' => [
                'index' => route('receivings.index'),
                'store' => route('receivings.store'),
                'search' => route('receivings.search-products'),
            ],
        ]);
    }

    public function store(StoreReceivingRequest $request): RedirectResponse
    {
        try {
            $this->receivingService->create(
                $request->validated(),
                $request->file('proof_image'),
                $request->user()?->id
            );

            return redirect()->route('receivings.index')->with('success', 'Receiving saved successfully. Stock quantities were updated.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Request $request, int $receiving): Response
    {
        $record = Receiving::query()->with(['receiver', 'items.product', 'location'])->findOrFail($receiving);
        $this->assertLocationAccess($request, (int) $record->location_id);

        $stocks = ProductStock::query()
            ->where('location_id', $record->location_id)
            ->whereIn('product_id', $record->items->pluck('product_id'))
            ->pluck('qty', 'product_id');

        return Inertia::render('inventory/receivings/show', [
            'record' => [
                'id' => $record->id,
                'number' => $record->receiving_number,
                'location' => $record->location->name ?? 'N/A',
                'delivered_by' => $record->delivered_by ?? 'N/A',
                'delivery_date' => $record->delivery_date ? Carbon::parse($record->delivery_date)->format('F d, Y') : 'N/A',
                'receiver' => $record->receiver->full_name ?? ($record->receiver->name ?? 'System'),
                'created' => $record->created_at?->format('M d, Y h:i A') ?? 'N/A',
                'remarks' => $record->remarks ?: 'No remarks provided.',
                'proof_url' => $record->proof_image ? route('receivings.proof', $record) : null,
                'items' => $record->items->values()->map(function ($item) use ($record, $stocks): array {
                    $delivered = (int) $item->qty_delivered;
                    $rolledBack = (int) ($item->qty_rolled_back ?? 0);
                    $remaining = max(0, $delivered - $rolledBack);
                    $stock = (int) ($stocks[$item->product_id] ?? 0);

                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'name' => $item->product->product_name ?? 'N/A',
                        'details' => $item->product->details ?? 'No details available.',
                        'delivered' => $delivered,
                        'rolled_back' => $rolledBack,
                        'remaining' => $remaining,
                        'stock' => $stock,
                        'rollback_limit' => min($remaining, $stock),
                        'rollback_url' => route('receivings.rollback', [$record->id, $item->id]),
                    ];
                }),
                'total_delivered' => (int) $record->items->sum('qty_delivered'),
            ],
            'can' => ['rollback' => (bool) $request->user()?->can('receivings.rollback')],
            'urls' => ['index' => route('receivings.index')],
        ]);
    }

    public function downloadProof(Request $request, Receiving $receiving): mixed
    {
        $this->assertLocationAccess($request, (int) $receiving->location_id);
        $proofPath = (string) $receiving->proof_image;
        abort_unless($proofPath !== '' && Storage::disk('local')->exists($proofPath), 404);

        return Storage::disk('local')->response($proofPath, basename($proofPath), ['Content-Disposition' => 'inline']);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $excludeIds = collect(explode(',', (string) $request->input('exclude_ids', '')))
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()->values()->all();

        return response()->json($this->productCatalogService->searchProducts(
            (string) $request->input('search', ''),
            $excludeIds
        ));
    }

    public function rollbackItem(RollbackReceivingItemRequest $request, int $receiving, int $item): RedirectResponse
    {
        $qty = (int) $request->validated('rollback_qty');

        try {
            $productName = $this->receivingService->rollbackItem($receiving, $item, $qty, $this->userLocationId($request));

            return redirect()->route('receivings.show', $receiving)->with('success', "{$productName} rollback completed. Quantity rolled back: {$qty}.");
        } catch (Throwable $e) {
            return redirect()->route('receivings.show', $receiving)->with('error', $e->getMessage());
        }
    }

    private function userLocationId(Request $request): ?int
    {
        $locationId = $request->user()?->location_id;

        return $locationId ? (int) $locationId : null;
    }

    private function assertLocationAccess(Request $request, int $locationId): void
    {
        $userLocationId = $this->userLocationId($request);
        if ($userLocationId && $userLocationId !== $locationId) {
            abort(403, 'You are not allowed to access this receiving record.');
        }
    }
}
