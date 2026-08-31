<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\RollbackReceivingItemRequest;
use App\Http\Requests\Maintenance\StoreReceivingRequest;
use App\Models\Receiving;
use App\Services\Maintenance\InventoryDirectoryService;
use App\Services\Maintenance\ProductCatalogService;
use App\Services\Maintenance\ReceivingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

final class ReceivingController extends Controller
{
    public function __construct(
        private readonly InventoryDirectoryService $directoryService,
        private readonly ProductCatalogService $productCatalogService,
        private readonly ReceivingService $receivingService,
    ) {}

    public function index(Request $request): View|string
    {
        $search = trim((string) $request->input('search', ''));
        $receivings = $this->directoryService->receivings($search, $this->userLocationId($request));

        if ($request->ajax()) {
            return view('maintenance.receive.table', compact('receivings'))->render();
        }

        return view('maintenance.receive.index', compact('receivings', 'search'));
    }

    public function create(Request $request): View
    {
        return view('maintenance.receive.create', [
            'products' => $this->directoryService->products(),
            'locations' => $this->directoryService->activeLocations($this->userLocationId($request)),
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

    public function show(Request $request, int $receiving): View
    {
        $record = Receiving::query()->with(['receiver', 'items.product', 'location'])->findOrFail($receiving);
        $this->assertLocationAccess($request, (int) $record->location_id);

        return view('maintenance.receive.show', ['receiving' => $record]);
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
