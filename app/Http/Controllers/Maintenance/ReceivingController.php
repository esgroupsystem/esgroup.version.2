<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\RollbackReceivingItemRequest;
use App\Http\Requests\Maintenance\StoreReceivingRequest;
use App\Http\Resources\Maintenance\ReceivingDetailResource;
use App\Http\Resources\Maintenance\ReceivingRowResource;
use App\Models\Receiving;
use App\Services\Maintenance\InventoryDirectoryService;
use App\Services\Maintenance\ProductCatalogService;
use App\Services\Maintenance\ReceivingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Inventory → Receiving Area. Users tied to a stockroom only see and receive into it. */
final class ReceivingController extends Controller
{
    private const LOCATION_DENIED = 'You are not allowed to access this receiving record.';

    public function __construct(
        private readonly InventoryDirectoryService $directory,
        private readonly ProductCatalogService $productCatalogService,
        private readonly ReceivingService $receivingService,
    ) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        return Inertia::render('inventory/receivings/index', [
            'records' => $this->receivingService->paginate($search, $this->directory->userLocationId($request))
                ->through(fn (Receiving $receiving): array => ReceivingRowResource::make($receiving)->resolve($request)),
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
        return Inertia::render('inventory/receivings/create', [
            'locations' => $this->directory->locationOptions($this->directory->userLocationId($request)),
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
        $this->receivingService->create($request->validated(), $request->file('proof_image'), $request->user()?->id);

        return redirect()->route('receivings.index')->with('success', 'Receiving saved successfully. Stock quantities were updated.');
    }

    public function show(Request $request, int $receiving): Response
    {
        $record = $this->receivingService->findForShow($receiving);
        $this->directory->assertLocationAccess($request, (int) $record->location_id, self::LOCATION_DENIED);

        return Inertia::render('inventory/receivings/show', [
            'record' => (new ReceivingDetailResource($record, $this->receivingService->currentStock($record)))->resolve($request),
            'can' => ['rollback' => (bool) $request->user()?->can('receivings.rollback')],
            'urls' => ['index' => route('receivings.index')],
        ]);
    }

    public function downloadProof(Request $request, Receiving $receiving): BinaryFileResponse
    {
        $this->directory->assertLocationAccess($request, (int) $receiving->location_id, self::LOCATION_DENIED);
        $path = $this->receivingService->proofPath($receiving);

        return response()->file($path, ['Content-Disposition' => 'inline; filename="'.basename($path).'"']);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        return response()->json($this->productCatalogService->searchProducts(
            (string) $request->input('search', ''),
            $this->directory->excludeIds($request)
        ));
    }

    public function rollbackItem(RollbackReceivingItemRequest $request, int $receiving, int $item): RedirectResponse
    {
        $qty = (int) $request->validated('rollback_qty');
        $productName = $this->receivingService->rollbackItem($receiving, $item, $qty, $this->directory->userLocationId($request));

        return redirect()->route('receivings.show', $receiving)->with('success', "{$productName} rollback completed. Quantity rolled back: {$qty}.");
    }
}
