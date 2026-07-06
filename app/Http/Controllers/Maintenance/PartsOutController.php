<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\BusDetail;
use App\Models\Location;
use App\Models\PartsOut;
use App\Models\PartsOutItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Services\Maintenance\PartsOutRollbackService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class PartsOutController extends Controller
{
    public function index(Request $request): View|string
    {
        $search = trim((string) $request->search);

        $partsOuts = PartsOut::query()
            ->with(['vehicle', 'creator', 'location'])
            ->withCount('items');

        $this->restrictLocation($partsOuts);

        $partsOuts = $partsOuts
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $q) use ($search): void {
                    $q->where('parts_out_number', 'like', "%{$search}%")
                        ->orWhere('mechanic_name', 'like', "%{$search}%")
                        ->orWhere('requested_by', 'like', "%{$search}%")
                        ->orWhere('job_order_no', 'like', "%{$search}%")
                        ->orWhere('issued_date', 'like', "%{$search}%")
                        ->orWhereHas('location', function (Builder $locationQuery) use ($search): void {
                            $locationQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('vehicle', function (Builder $vehicleQuery) use ($search): void {
                            $vehicleQuery->where('plate_number', 'like', "%{$search}%")
                                ->orWhere('body_number', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('garage', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('maintenance.parts_out.table', compact('partsOuts'))->render();
        }

        return view('maintenance.parts_out.index', compact('partsOuts', 'search'));
    }

    public function create(): View
    {
        $vehicles = BusDetail::query()
            ->orderBy('plate_number')
            ->get();

        $locationId = $this->userLocationId();

        $locations = Location::query()
            ->where('is_active', 1)
            ->when($locationId, function (Builder $query) use ($locationId): void {
                $query->where('id', $locationId);
            })
            ->orderBy('name')
            ->get();

        return view('maintenance.parts_out.create', compact('vehicles', 'locations'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(
            $request->user()->can('parts-out.create'),
            403,
            'You are not authorized to create Parts Out transactions.'
        );

        $validated = $request->validate([
            'vehicle_id' => ['nullable', 'exists:bus_details,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'mechanic_name' => ['required', 'string', 'max:255'],
            'requested_by' => ['nullable', 'string', 'max:255'],
            'issued_date' => ['required', 'date'],
            'job_order_no' => ['nullable', 'string', 'max:255'],
            'odometer' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],

            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'integer', 'exists:products,id', 'distinct'],

            'qty_used' => ['required', 'array', 'min:1'],
            'qty_used.*' => ['required', 'integer', 'min:1'],

            'item_remarks' => ['nullable', 'array'],
            'item_remarks.*' => ['nullable', 'string', 'max:1000'],
        ]);

        if (count($validated['product_id']) !== count($validated['qty_used'])) {
            return back()
                ->withInput()
                ->with('error', 'Product count and quantity count do not match.');
        }

        $userLocationId = $this->userLocationId();

        if ($userLocationId && (int) $validated['location_id'] !== (int) $userLocationId) {
            return back()
                ->withInput()
                ->with('error', 'You are only allowed to issue parts from your assigned garage.');
        }

        try {
            $partsOut = DB::transaction(function () use ($validated): PartsOut {
                $location = Location::query()
                    ->whereKey($validated['location_id'])
                    ->firstOrFail();

                $partsOut = PartsOut::query()->create([
                    'parts_out_number' => 'TEMP',
                    'vehicle_id' => $validated['vehicle_id'] ?? null,
                    'location_id' => $validated['location_id'],
                    'mechanic_name' => $validated['mechanic_name'],
                    'requested_by' => $validated['requested_by'] ?? null,
                    'issued_date' => $validated['issued_date'],
                    'job_order_no' => $validated['job_order_no'] ?? null,
                    'odometer' => $validated['odometer'] ?? null,
                    'purpose' => $validated['purpose'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                    'status' => 'posted',
                    'created_by' => Auth::id(),
                ]);

                $partsOutNumber = $this->generatePartsOutNumber($partsOut);

                $partsOut->update([
                    'parts_out_number' => $partsOutNumber,
                ]);

                foreach ($validated['product_id'] as $index => $productId) {
                    $qtyUsed = (int) $validated['qty_used'][$index];

                    $product = Product::query()
                        ->whereKey($productId)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $productStock = ProductStock::query()
                        ->where('product_id', $product->id)
                        ->where('location_id', $validated['location_id'])
                        ->lockForUpdate()
                        ->first();

                    if (! $productStock) {
                        throw new \RuntimeException(
                            "No stock record found for {$product->product_name} at {$location->name}."
                        );
                    }

                    $stockBefore = (int) $productStock->qty;

                    if ($stockBefore < $qtyUsed) {
                        throw new \RuntimeException(
                            "Insufficient stock for {$product->product_name} at {$location->name}. Available: {$stockBefore}, Requested: {$qtyUsed}."
                        );
                    }

                    $stockAfter = $stockBefore - $qtyUsed;

                    PartsOutItem::query()->create([
                        'parts_out_id' => $partsOut->id,
                        'product_id' => $product->id,
                        'qty_used' => $qtyUsed,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'remarks' => $validated['item_remarks'][$index] ?? null,
                    ]);

                    $productStock->update([
                        'qty' => $stockAfter,
                    ]);

                    $this->syncProductTotalStock((int) $product->id);

                    StockMovement::query()->create([
                        'product_id' => $product->id,
                        'location_id' => $validated['location_id'],
                        'reference_type' => 'parts_out',
                        'reference_id' => $partsOut->id,
                        'movement_type' => 'out',
                        'qty' => $qtyUsed,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'transaction_date' => $validated['issued_date'],
                        'remarks' => 'Parts Out #'.$partsOutNumber,
                        'created_by' => Auth::id(),
                    ]);
                }

                return $partsOut;
            }, 3);

            return redirect()
                ->route('parts-out.show', $partsOut->id)
                ->with('success', 'Parts Out transaction saved successfully. Stock quantities were deducted.');
        } catch (Throwable $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(PartsOut $partsOut): View
    {
        $partsOut->load(['vehicle', 'creator', 'location', 'items.product']);

        $locationId = $this->userLocationId();

        if ($locationId && (int) $partsOut->location_id !== (int) $locationId) {
            abort(403, 'You are not allowed to view this Parts Out record.');
        }

        return view('maintenance.parts_out.show', compact('partsOut'));
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $search = trim((string) $request->get('search', ''));
        $locationId = (int) $request->get('location_id');

        if (! $locationId || strlen($search) < 2) {
            return response()->json([]);
        }

        $userLocationId = $this->userLocationId();

        if ($userLocationId && $locationId !== (int) $userLocationId) {
            return response()->json([]);
        }

        $excludeIds = collect(explode(',', (string) $request->get('exclude_ids', '')))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $products = Product::query()
            ->with([
                'category',
                'stocks' => function ($query) use ($locationId): void {
                    $query->where('location_id', $locationId);
                },
            ])
            ->when(! empty($excludeIds), function (Builder $query) use ($excludeIds): void {
                $query->whereNotIn('id', $excludeIds);
            })
            ->where(function (Builder $query) use ($search): void {
                $query->where('product_name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%");
            })
            ->orderBy('product_name')
            ->limit(20)
            ->get()
            ->map(function (Product $product): array {
                $stockRow = $product->stocks->first();
                $stockQty = $stockRow ? (int) $stockRow->qty : 0;

                return [
                    'id' => $product->id,
                    'name' => $product->product_name,
                    'supplier_name' => $product->supplier_name,
                    'category' => optional($product->category)->name,
                    'unit' => $product->unit,
                    'part_number' => $product->part_number,
                    'details' => $product->details,
                    'stock' => $stockQty,
                ];
            })
            ->filter(fn (array $product): bool => $product['stock'] > 0)
            ->values();

        return response()->json($products);
    }

    public function rollback(
        Request $request,
        PartsOut $partsOut,
        PartsOutRollbackService $rollbackService
    ): RedirectResponse {
        abort_unless(
            $request->user()->can('parts-out.rollback'),
            403,
            'You are not authorized to rollback Parts Out transactions.'
        );

        $validated = $request->validate([
            'rollback_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $userLocationId = $this->userLocationId();

        if ($userLocationId && (int) $partsOut->location_id !== (int) $userLocationId) {
            abort(403, 'You are not allowed to rollback this Parts Out record.');
        }

        try {
            $rollbackService->rollback(
                partsOutId: $partsOut->id,
                reason: $validated['rollback_reason'] ?? null
            );

            return redirect()
                ->route('parts-out.show', $partsOut->id)
                ->with('success', 'Parts Out transaction rolled back successfully. Stock has been returned.');
        } catch (Throwable $e) {
            return back()
                ->with('error', $e->getMessage());
        }
    }

    private function generatePartsOutNumber(PartsOut $partsOut): string
    {
        return 'POUT-'.now()->format('Y').'-'.str_pad((string) $partsOut->id, 5, '0', STR_PAD_LEFT);
    }

    private function syncProductTotalStock(int $productId): void
    {
        $totalStock = ProductStock::query()
            ->where('product_id', $productId)
            ->sum('qty');

        Product::query()
            ->whereKey($productId)
            ->update([
                'stock_qty' => $totalStock,
            ]);
    }

    private function userLocationId(): ?int
    {
        $locationId = Auth::user()->location_id ?? null;

        return $locationId ? (int) $locationId : null;
    }

    private function restrictLocation(Builder $query): Builder
    {
        $locationId = $this->userLocationId();

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query;
    }
}
