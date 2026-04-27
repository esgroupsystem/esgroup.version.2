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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PartsOutController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->search);

        $partsOuts = PartsOut::with(['vehicle', 'creator', 'location']);

        $this->restrictLocation($partsOuts);

        $partsOuts = $partsOuts
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('parts_out_number', 'like', "%{$search}%")
                        ->orWhere('mechanic_name', 'like', "%{$search}%")
                        ->orWhere('requested_by', 'like', "%{$search}%")
                        ->orWhere('job_order_no', 'like', "%{$search}%")
                        ->orWhere('issued_date', 'like', "%{$search}%")
                        ->orWhereHas('location', function ($locationQuery) use ($search) {
                            $locationQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
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
            return response()->json([
                'success' => true,
                'html' => view('maintenance.parts_out.table', compact('partsOuts'))->render(),
            ]);
        }

        return view('maintenance.parts_out.index', compact('partsOuts', 'search'));
    }

    public function create()
    {
        $vehicles = BusDetail::orderBy('plate_number')->get();

        $locationId = $this->userLocationId();

        $locations = Location::query()
            ->when($locationId, function ($query) use ($locationId) {
                $query->where('id', $locationId);
            })
            ->orderBy('name')
            ->get();

        return view('maintenance.parts_out.create', compact('vehicles', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:bus_details,id',
            'location_id' => 'required|exists:locations,id',
            'mechanic_name' => 'required|string|max:255',
            'requested_by' => 'nullable|string|max:255',
            'issued_date' => 'required|date',
            'job_order_no' => 'nullable|string|max:255',
            'odometer' => 'nullable|string|max:255',
            'purpose' => 'nullable|string',
            'remarks' => 'nullable|string',

            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|integer|exists:products,id|distinct',

            'qty_used' => 'required|array|min:1',
            'qty_used.*' => 'required|integer|min:1',

            'item_remarks' => 'nullable|array',
            'item_remarks.*' => 'nullable|string',
        ]);

        if (count($validated['product_id']) !== count($validated['qty_used'])) {
            return back()
                ->withInput()
                ->with('error', 'Product count and quantity count do not match.');
        }

        $locationId = $this->userLocationId();

        if ($locationId && (int) $validated['location_id'] !== (int) $locationId) {
            return back()
                ->withInput()
                ->with('error', 'You are only allowed to issue parts from your assigned garage.');
        }

        DB::beginTransaction();

        try {
            $location = Location::findOrFail($validated['location_id']);

            $partsOut = PartsOut::create([
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

            $partsOutNumber = 'POUT-'.now()->format('Y').'-'.str_pad($partsOut->id, 5, '0', STR_PAD_LEFT);

            $partsOut->update([
                'parts_out_number' => $partsOutNumber,
            ]);

            foreach ($validated['product_id'] as $index => $productId) {
                $qtyUsed = (int) $validated['qty_used'][$index];

                $product = Product::lockForUpdate()->findOrFail($productId);

                $productStock = ProductStock::lockForUpdate()->firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'location_id' => $validated['location_id'],
                    ],
                    [
                        'qty' => 0,
                    ]
                );

                $stockBefore = (int) $productStock->qty;

                if ($stockBefore < $qtyUsed) {
                    throw new \Exception(
                        "Insufficient stock for {$product->product_name} at {$location->name}. Available: {$stockBefore}, Requested: {$qtyUsed}."
                    );
                }

                $stockAfter = $stockBefore - $qtyUsed;

                PartsOutItem::create([
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

                $product->update([
                    'stock_qty' => ProductStock::where('product_id', $product->id)->sum('qty'),
                ]);

                StockMovement::create([
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

            DB::commit();

            return redirect()
                ->route('parts-out.show', $partsOut->id)
                ->with('success', 'Parts Out transaction saved successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(PartsOut $partsOut)
    {
        $partsOut->load(['vehicle', 'creator', 'location', 'items.product']);

        $locationId = $this->userLocationId();

        if ($locationId && (int) $partsOut->location_id !== (int) $locationId) {
            abort(403, 'You are not allowed to view this Parts Out record.');
        }

        return view('maintenance.parts_out.show', compact('partsOut'));
    }

    public function searchProducts(Request $request)
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

        $products = Product::with([
            'category',
            'stocks' => function ($query) use ($locationId) {
                $query->where('location_id', $locationId);
            },
        ])
            ->when(! empty($excludeIds), function ($query) use ($excludeIds) {
                $query->whereNotIn('id', $excludeIds);
            })
            ->where(function ($query) use ($search) {
                $query->where('product_name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%");
            })
            ->orderBy('product_name')
            ->limit(20)
            ->get()
            ->map(function ($product) {
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
            ->filter(fn ($product) => $product['stock'] > 0)
            ->values();

        return response()->json($products);
    }

    public function rollback(PartsOut $partsOut)
    {
        $userLocationId = $this->userLocationId();

        if ($userLocationId && (int) $partsOut->location_id !== (int) $userLocationId) {
            abort(403, 'You are not allowed to rollback this Parts Out record.');
        }

        DB::transaction(function () use ($partsOut) {
            $partsOut->load(['items.product', 'location']);

            if ($partsOut->status !== 'posted') {
                throw new \Exception('Only posted Parts Out transactions can be rolled back.');
            }

            foreach ($partsOut->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);

                $productStock = ProductStock::lockForUpdate()->firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'location_id' => $partsOut->location_id,
                    ],
                    [
                        'qty' => 0,
                    ]
                );

                $stockBefore = (int) $productStock->qty;
                $qtyToReturn = (int) $item->qty_used;
                $stockAfter = $stockBefore + $qtyToReturn;

                $productStock->update([
                    'qty' => $stockAfter,
                ]);

                $product->update([
                    'stock_qty' => ProductStock::where('product_id', $item->product_id)->sum('qty'),
                ]);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'location_id' => $partsOut->location_id,
                    'reference_type' => 'parts_out_rollback',
                    'reference_id' => $partsOut->id,
                    'movement_type' => 'in',
                    'qty' => $qtyToReturn,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'transaction_date' => now()->toDateString(),
                    'remarks' => 'Rollback of Parts Out #'.$partsOut->parts_out_number,
                    'created_by' => Auth::id(),
                ]);
            }

            $partsOut->update([
                'status' => 'rolled_back',
            ]);
        });

        return redirect()
            ->route('parts-out.show', $partsOut->id)
            ->with('success', 'Parts Out transaction rolled back successfully. Stock has been returned.');
    }

    private function userLocationId()
    {
        return Auth::user()->location_id ?? null;
    }

    private function restrictLocation($query)
    {
        $locationId = $this->userLocationId();

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query;
    }
}
