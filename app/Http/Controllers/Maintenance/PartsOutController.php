<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\RollbackInventoryRequest;
use App\Http\Requests\Maintenance\StorePartsOutRequest;
use App\Http\Resources\Maintenance\PartsOutDetailResource;
use App\Http\Resources\Maintenance\PartsOutRowResource;
use App\Models\PartsOut;
use App\Services\Maintenance\InventoryDirectoryService;
use App\Services\Maintenance\PartsOutService;
use App\Services\Maintenance\ProductCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Maintenance → Parts Issuance. Users tied to a stockroom only see and issue from it. */
final class PartsOutController extends Controller
{
    private const LOCATION_DENIED = 'You are not allowed to access this Parts Out record.';

    public function __construct(
        private readonly InventoryDirectoryService $directory,
        private readonly ProductCatalogService $productCatalogService,
        private readonly PartsOutService $partsOutService,
    ) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));

        return Inertia::render('inventory/parts-out/index', [
            'records' => $this->partsOutService->paginate($search, $this->directory->userLocationId($request))
                ->through(fn (PartsOut $row): array => PartsOutRowResource::make($row)->resolve($request)),
            'filters' => ['search' => $search],
            'can' => ['create' => (bool) $request->user()?->can('parts-out.create')],
            'urls' => [
                'index' => route('parts-out.index'),
                'create' => route('parts-out.create'),
                'dashboard' => route('items.dashboard'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('inventory/parts-out/create', [
            'vehicles' => $this->directory->vehicleOptions(),
            'locations' => $this->directory->locationOptions($this->directory->userLocationId($request)),
            'today' => now()->format('Y-m-d'),
            'urls' => [
                'index' => route('parts-out.index'),
                'store' => route('parts-out.store'),
                'search' => route('parts-out.search-products'),
            ],
        ]);
    }

    public function store(StorePartsOutRequest $request): RedirectResponse
    {
        $partsOut = $this->partsOutService->create($request->validated(), $request->user()?->id);

        return redirect()->route('parts-out.show', $partsOut)->with('success', 'Parts Out transaction saved successfully. Stock quantities were deducted.');
    }

    public function show(Request $request, PartsOut $partsOut): Response
    {
        $this->directory->assertLocationAccess($request, (int) $partsOut->location_id, self::LOCATION_DENIED);

        return Inertia::render('inventory/parts-out/show', [
            'record' => PartsOutDetailResource::make($this->partsOutService->loadForShow($partsOut))->resolve($request),
            'can' => ['rollback' => $partsOut->status === 'posted' && (bool) $request->user()?->can('parts-out.rollback')],
            'urls' => [
                'index' => route('parts-out.index'),
                'rollback' => route('parts-out.rollback', $partsOut),
            ],
        ]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $locationId = (int) $request->input('location_id');
        if (! $locationId || mb_strlen(trim((string) $request->input('search', ''))) < 2) {
            return response()->json([]);
        }

        $userLocationId = $this->directory->userLocationId($request);
        if ($userLocationId && $locationId !== $userLocationId) {
            return response()->json([]);
        }

        return response()->json($this->productCatalogService->searchProducts(
            (string) $request->input('search', ''),
            $this->directory->excludeIds($request),
            $locationId,
            true
        ));
    }

    public function rollback(RollbackInventoryRequest $request, PartsOut $partsOut): RedirectResponse
    {
        abort_unless($request->user()?->can('parts-out.rollback'), 403);
        $this->directory->assertLocationAccess($request, (int) $partsOut->location_id, self::LOCATION_DENIED);

        $this->partsOutService->rollback($partsOut->id, $request->validated('rollback_reason'), $request->user()?->id);

        // Rollback soft-deletes the record, so its detail page no longer exists.
        return redirect()->route('parts-out.index')->with('success', 'Parts Out transaction rolled back successfully. Stock has been returned.');
    }
}
