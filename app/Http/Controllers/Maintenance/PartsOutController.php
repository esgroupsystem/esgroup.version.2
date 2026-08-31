<?php

declare(strict_types=1);

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\RollbackInventoryRequest;
use App\Http\Requests\Maintenance\StorePartsOutRequest;
use App\Models\PartsOut;
use App\Services\Maintenance\InventoryDirectoryService;
use App\Services\Maintenance\PartsOutRollbackService;
use App\Services\Maintenance\PartsOutService;
use App\Services\Maintenance\ProductCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class PartsOutController extends Controller
{
    public function __construct(
        private readonly InventoryDirectoryService $directoryService,
        private readonly ProductCatalogService $productCatalogService,
        private readonly PartsOutService $partsOutService,
        private readonly PartsOutRollbackService $rollbackService,
    ) {}

    public function index(Request $request): View|string
    {
        $search = trim((string) $request->input('search', ''));
        $partsOuts = $this->directoryService->partsOuts($search, $this->userLocationId($request));

        if ($request->ajax()) {
            return view('maintenance.parts_out.table', compact('partsOuts'))->render();
        }

        return view('maintenance.parts_out.index', compact('partsOuts', 'search'));
    }

    public function create(Request $request): View
    {
        return view('maintenance.parts_out.create', [
            'vehicles' => $this->directoryService->vehicles(),
            'locations' => $this->directoryService->activeLocations($this->userLocationId($request)),
        ]);
    }

    public function store(StorePartsOutRequest $request): RedirectResponse
    {
        try {
            $partsOut = $this->partsOutService->create($request->validated(), $request->user()?->id);

            return redirect()->route('parts-out.show', $partsOut)->with('success', 'Parts Out transaction saved successfully. Stock quantities were deducted.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Request $request, PartsOut $partsOut): View
    {
        $this->assertLocationAccess($request, (int) $partsOut->location_id);
        $partsOut->load(['vehicle', 'creator', 'location', 'items.product']);

        return view('maintenance.parts_out.show', compact('partsOut'));
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $locationId = (int) $request->input('location_id');
        if (! $locationId || mb_strlen(trim((string) $request->input('search', ''))) < 2) {
            return response()->json([]);
        }

        $userLocationId = $this->userLocationId($request);
        if ($userLocationId && $locationId !== $userLocationId) {
            return response()->json([]);
        }

        $excludeIds = collect(explode(',', (string) $request->input('exclude_ids', '')))
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()->values()->all();

        return response()->json($this->productCatalogService->searchProducts(
            (string) $request->input('search', ''),
            $excludeIds,
            $locationId,
            true
        ));
    }

    public function rollback(RollbackInventoryRequest $request, PartsOut $partsOut): RedirectResponse
    {
        abort_unless($request->user()?->can('parts-out.rollback'), 403);
        $this->assertLocationAccess($request, (int) $partsOut->location_id);

        try {
            $this->rollbackService->rollback($partsOut->id, $request->validated('rollback_reason'));

            return redirect()->route('parts-out.show', $partsOut->id)->with('success', 'Parts Out transaction rolled back successfully. Stock has been returned.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
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
            abort(403, 'You are not allowed to access this Parts Out record.');
        }
    }
}
