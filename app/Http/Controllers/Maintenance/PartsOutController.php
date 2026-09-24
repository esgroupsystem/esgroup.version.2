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
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

final class PartsOutController extends Controller
{
    public function __construct(
        private readonly InventoryDirectoryService $directoryService,
        private readonly ProductCatalogService $productCatalogService,
        private readonly PartsOutService $partsOutService,
        private readonly PartsOutRollbackService $rollbackService,
    ) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $user = $request->user();

        return Inertia::render('inventory/parts-out/index', [
            'records' => $this->directoryService->partsOuts($search, $this->userLocationId($request))
                ->through(fn (PartsOut $row): array => [
                    'id' => $row->id,
                    'number' => $row->parts_out_number,
                    'vehicle' => $row->vehicle ? [
                        'plate_number' => $row->vehicle->plate_number ?? 'N/A',
                        'detail' => trim(($row->vehicle->body_number ?? 'No Body No.').($row->vehicle->name ? ' | '.$row->vehicle->name : '')),
                    ] : null,
                    'location' => $row->location->name ?? 'N/A',
                    'mechanic' => $row->mechanic_name ?? '—',
                    'date' => $row->issued_date ? Carbon::parse($row->issued_date)->format('M d, Y') : null,
                    'day' => $row->issued_date ? Carbon::parse($row->issued_date)->format('l') : null,
                    'job_order_no' => $row->job_order_no,
                    'items_count' => (int) ($row->items_count ?? 0),
                    'status' => self::status($row->status),
                    'creator' => $row->creator->full_name ?? ($row->creator->name ?? '—'),
                    'show_url' => route('parts-out.show', $row->id),
                ]),
            'filters' => ['search' => $search],
            'can' => ['create' => (bool) $user?->can('parts-out.create')],
            'urls' => [
                'index' => route('parts-out.index'),
                'create' => route('parts-out.create'),
                'dashboard' => route('items.dashboard'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $locations = $this->directoryService->activeLocations($this->userLocationId($request));

        return Inertia::render('inventory/parts-out/create', [
            'vehicles' => $this->directoryService->vehicles()->map(fn ($vehicle): array => [
                'value' => (string) $vehicle->id,
                'label' => ($vehicle->plate_number ?? 'N/A').' — '.($vehicle->body_number ?? 'No Body No.'),
                'hint' => trim(($vehicle->name ?? '').($vehicle->garage ? ' · '.$vehicle->garage : '')),
            ])->values(),
            'locations' => $locations->map(fn ($location): array => ['value' => (string) $location->id, 'label' => (string) $location->name])->values(),
            'today' => now()->format('Y-m-d'),
            'urls' => [
                'index' => route('parts-out.index'),
                'store' => route('parts-out.store'),
                'search' => route('parts-out.search-products'),
            ],
        ]);
    }

    /** @return array{key: string, label: string} */
    private static function status(?string $status): array
    {
        $key = strtolower((string) $status);

        return [
            'key' => $key,
            'label' => match ($key) {
                'posted' => 'Posted',
                'cancelled' => 'Cancelled',
                'rolled_back' => 'Rolled Back',
                default => ucfirst(str_replace('_', ' ', (string) ($status ?? 'N/A'))),
            },
        ];
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

    public function show(Request $request, PartsOut $partsOut): Response
    {
        $this->assertLocationAccess($request, (int) $partsOut->location_id);
        $partsOut->load(['vehicle', 'creator', 'location', 'items.product']);

        return Inertia::render('inventory/parts-out/show', [
            'record' => [
                'id' => $partsOut->id,
                'number' => $partsOut->parts_out_number,
                'status' => self::status($partsOut->status),
                'date' => $partsOut->issued_date ? Carbon::parse($partsOut->issued_date)->format('M d, Y') : 'N/A',
                'mechanic' => $partsOut->mechanic_name ?: '—',
                'vehicle' => $partsOut->vehicle ? [
                    'plate_number' => $partsOut->vehicle->plate_number ?? 'N/A',
                    'detail' => collect([
                        'Body No.: '.($partsOut->vehicle->body_number ?? 'N/A'),
                        $partsOut->vehicle->name,
                        $partsOut->vehicle->garage ? 'Garage: '.$partsOut->vehicle->garage : null,
                    ])->filter()->implode(' | '),
                ] : null,
                'location' => $partsOut->location->name ?? 'N/A',
                'creator' => $partsOut->creator->full_name ?? ($partsOut->creator->name ?? '—'),
                'requested_by' => $partsOut->requested_by ?: '—',
                'job_order_no' => $partsOut->job_order_no ?: '—',
                'odometer' => $partsOut->odometer ?: '—',
                'purpose' => $partsOut->purpose ?: '—',
                'remarks' => $partsOut->remarks ?: '—',
                'items' => $partsOut->items->map(fn ($item): array => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->product_name ?? 'N/A',
                    'supplier' => $item->product->supplier_name ?? '—',
                    'unit' => $item->product->unit ?? '—',
                    'part_number' => $item->product->part_number ?? '—',
                    'qty_used' => (int) $item->qty_used,
                    'stock_before' => (int) $item->stock_before,
                    'stock_after' => (int) $item->stock_after,
                    'remarks' => $item->remarks ?: '—',
                ])->values(),
                'total_qty' => (int) $partsOut->items->sum('qty_used'),
            ],
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

            // Rollback soft-deletes the record, so its detail page no longer exists.
            return redirect()->route('parts-out.index')->with('success', 'Parts Out transaction rolled back successfully. Stock has been returned.');
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
