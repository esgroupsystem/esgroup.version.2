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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PartsOutController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->search);

            $partsOuts = PartsOut::with(['vehicle', 'creator', 'location'])
                ->when($search, function ($query) use ($search) {
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
        } catch (Throwable $e) {
            Log::error('PartsOutController@index failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load Parts Out records.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            flash('Failed to load Parts Out records. Please check the logs.')->error();

            return back();
        }
    }

    public function create()
    {
        try {
            $vehicles = BusDetail::orderBy('plate_number')->get();
            $locations = Location::orderBy('name')->get();

            return view('maintenance.parts_out.create', compact('vehicles', 'locations'));
        } catch (Throwable $e) {
            Log::error('PartsOutController@create failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash('Failed to load Parts Out form.')->error();

            return back();
        }
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
        ], [
            'location_id.required' => 'Please select source garage / location.',
            'location_id.exists' => 'Selected location does not exist.',
            'vehicle_id.exists' => 'Selected vehicle does not exist.',
            'mechanic_name.required' => 'Mechanic name is required.',
            'issued_date.required' => 'Issued date is required.',
            'issued_date.date' => 'Issued date must be a valid date.',
            'product_id.required' => 'At least one product is required.',
            'product_id.array' => 'Product list format is invalid.',
            'product_id.min' => 'Please add at least one product.',
            'product_id.*.exists' => 'One of the selected products does not exist.',
            'product_id.*.distinct' => 'Duplicate products are not allowed in the same transaction.',
            'qty_used.required' => 'Quantity used is required.',
            'qty_used.array' => 'Quantity list format is invalid.',
            'qty_used.min' => 'Please provide quantity for at least one item.',
            'qty_used.*.integer' => 'Quantity used must be a whole number.',
            'qty_used.*.min' => 'Quantity used must be at least 1.',
        ]);

        if (count($validated['product_id']) !== count($validated['qty_used'])) {
            return back()
                ->withInput()
                ->with('error', 'Product count and quantity count do not match.');
        }

        DB::beginTransaction();

        try {
            $location = Location::find($validated['location_id']);

            if (! $location) {
                throw new \Exception('Selected source location was not found.');
            }

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
                $rowNumber = $index + 1;
                $qtyUsed = (int) ($validated['qty_used'][$index] ?? 0);

                if ($qtyUsed <= 0) {
                    throw new \Exception("Quantity used must be greater than zero for row {$rowNumber}.");
                }

                $product = Product::lockForUpdate()->find($productId);

                if (! $product) {
                    throw new ModelNotFoundException("Product not found for row {$rowNumber}.");
                }

                $productStock = ProductStock::lockForUpdate()->firstOrCreate(
                    [
                        'product_id' => $productId,
                        'location_id' => $validated['location_id'],
                    ],
                    [
                        'qty' => 0,
                    ]
                );

                $stockBefore = (int) $productStock->qty;

                if ($stockBefore < $qtyUsed) {
                    throw new \Exception(
                        "Insufficient stock for product: {$product->product_name} at {$location->name}. ".
                        "Available: {$stockBefore}, Requested: {$qtyUsed}."
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
                    'stock_qty' => ProductStock::where('product_id', $productId)->sum('qty'),
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
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            Log::warning('PartsOutController@store model not found', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'One of the selected records was not found. Please refresh and try again.');
        } catch (QueryException $e) {
            DB::rollBack();

            Log::error('PartsOutController@store database error', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'request' => $request->all(),
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Database error while saving Parts Out transaction. Please check the logs.');
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('PartsOutController@store failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(PartsOut $partsOut)
    {
        try {
            $partsOut->load(['vehicle', 'creator', 'location', 'items.product']);

            return view('maintenance.parts_out.show', compact('partsOut'));
        } catch (Throwable $e) {
            Log::error('PartsOutController@show failed', [
                'parts_out_id' => $partsOut->id ?? null,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash('Failed to load Parts Out details.')->error();

            return back();
        }
    }

    public function searchProducts(Request $request)
    {
        try {
            $search = trim((string) $request->get('search', ''));
            $locationId = (int) $request->get('location_id');

            $excludeIds = collect(explode(',', (string) $request->get('exclude_ids', '')))
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if (! $locationId || strlen($search) < 2) {
                return response()->json([]);
            }

            $location = Location::find($locationId);

            if (! $location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected location is invalid.',
                ], 422);
            }

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
                ->filter(function ($product) {
                    return $product['stock'] > 0;
                })
                ->values();

            return response()->json($products);
        } catch (Throwable $e) {
            Log::error('PartsOutController@searchProducts failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to search products.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
