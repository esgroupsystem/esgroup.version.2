<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->get('search', ''));

            $transfers = StockTransfer::with(['fromLocation', 'toLocation', 'creator'])
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('transfer_number', 'like', "%{$search}%")
                            ->orWhere('requested_by', 'like', "%{$search}%")
                            ->orWhere('received_by', 'like', "%{$search}%")
                            ->orWhere('remarks', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'html' => view('maintenance.stock_transfers.table', compact('transfers'))->render(),
                ]);
            }

            return view('maintenance.stock_transfers.index', compact('transfers', 'search'));
        } catch (Throwable $e) {
            Log::error('StockTransferController@index failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load stock transfers.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            flash('Failed to load stock transfers. Please check the logs.')->error();

            return back();
        }
    }

    public function create()
    {
        try {
            $locations = Location::where('is_active', true)
                ->orderBy('name')
                ->get();

            return view('maintenance.stock_transfers.create', compact('locations'));
        } catch (Throwable $e) {
            Log::error('StockTransferController@create failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash('Failed to load stock transfer form.')->error();

            return back();
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_location_id' => ['required', 'integer', 'exists:locations,id'],
            'to_location_id' => ['required', 'integer', 'exists:locations,id', 'different:from_location_id'],
            'transfer_date' => ['required', 'date'],
            'requested_by' => ['nullable', 'string', 'max:255'],
            'received_by' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],

            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'integer', 'exists:products,id'],

            'qty' => ['required', 'array', 'min:1'],
            'qty.*' => ['required', 'integer', 'min:1'],
        ], [
            'from_location_id.required' => 'From location is required.',
            'from_location_id.exists' => 'Selected source location does not exist.',
            'to_location_id.required' => 'To location is required.',
            'to_location_id.exists' => 'Selected destination location does not exist.',
            'to_location_id.different' => 'From location and To location must be different.',
            'transfer_date.required' => 'Transfer date is required.',
            'transfer_date.date' => 'Transfer date must be a valid date.',
            'product_id.required' => 'At least one product is required.',
            'product_id.array' => 'Product list format is invalid.',
            'product_id.min' => 'Please add at least one product.',
            'product_id.*.exists' => 'One of the selected products does not exist.',
            'qty.required' => 'Quantity is required.',
            'qty.array' => 'Quantity list format is invalid.',
            'qty.min' => 'Please provide quantity for at least one product.',
            'qty.*.integer' => 'Each quantity must be a whole number.',
            'qty.*.min' => 'Each quantity must be at least 1.',
        ]);

        if (count($validated['product_id']) !== count($validated['qty'])) {
            return back()
                ->withInput()
                ->with('error', 'Product count and quantity count do not match.');
        }

        if (count($validated['product_id']) !== count(array_unique($validated['product_id']))) {
            return back()
                ->withInput()
                ->with('error', 'Duplicate products are not allowed in the same transfer.');
        }

        DB::beginTransaction();

        try {
            $fromLocation = Location::where('id', $validated['from_location_id'])
                ->where('is_active', true)
                ->first();

            $toLocation = Location::where('id', $validated['to_location_id'])
                ->where('is_active', true)
                ->first();

            if (! $fromLocation) {
                throw new \Exception('Source location is inactive or not found.');
            }

            if (! $toLocation) {
                throw new \Exception('Destination location is inactive or not found.');
            }

            $transfer = StockTransfer::create([
                'transfer_number' => 'TEMP',
                'from_location_id' => $validated['from_location_id'],
                'to_location_id' => $validated['to_location_id'],
                'transfer_date' => $validated['transfer_date'],
                'requested_by' => $validated['requested_by'] ?? null,
                'received_by' => $validated['received_by'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $transferNumber = 'ST-'.now()->format('Y').'-'.str_pad($transfer->id, 5, '0', STR_PAD_LEFT);

            $transfer->update([
                'transfer_number' => $transferNumber,
            ]);

            foreach ($validated['product_id'] as $index => $productId) {
                $rowNumber = $index + 1;
                $qty = (int) ($validated['qty'][$index] ?? 0);

                if ($qty <= 0) {
                    throw new \Exception("Quantity must be greater than zero on row {$rowNumber}.");
                }

                $product = Product::find($productId);

                if (! $product) {
                    throw new ModelNotFoundException("Product not found on row {$rowNumber}.");
                }

                $fromStock = ProductStock::lockForUpdate()->firstOrCreate(
                    [
                        'product_id' => $productId,
                        'location_id' => $validated['from_location_id'],
                    ],
                    [
                        'qty' => 0,
                    ]
                );

                $toStock = ProductStock::lockForUpdate()->firstOrCreate(
                    [
                        'product_id' => $productId,
                        'location_id' => $validated['to_location_id'],
                    ],
                    [
                        'qty' => 0,
                    ]
                );

                $fromQtyBefore = (int) $fromStock->qty;
                $toQtyBefore = (int) $toStock->qty;

                if ($fromQtyBefore < $qty) {
                    throw new \Exception(
                        "Insufficient stock for product: {$product->product_name} on row {$rowNumber}. ".
                        "Available in {$fromLocation->name}: {$fromQtyBefore}, Requested: {$qty}."
                    );
                }

                $fromQtyAfter = $fromQtyBefore - $qty;
                $toQtyAfter = $toQtyBefore + $qty;

                $fromStock->update([
                    'qty' => $fromQtyAfter,
                ]);

                $toStock->update([
                    'qty' => $toQtyAfter,
                ]);

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $productId,
                    'qty' => $qty,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('stock-transfers.show', $transfer->id)
                ->with('success', 'Stock transfer created successfully.');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            Log::warning('StockTransferController@store model not found', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'One of the selected records was not found. Please refresh and try again.');
        } catch (QueryException $e) {
            DB::rollBack();

            Log::error('StockTransferController@store database error', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'request' => $request->all(),
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Database error while saving stock transfer. Please check the logs.');
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('StockTransferController@store failed', [
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

    public function show(StockTransfer $stock_transfer)
    {
        try {
            $stock_transfer->load([
                'fromLocation',
                'toLocation',
                'creator',
                'items.product.category',
            ]);

            $transfer = $stock_transfer;

            return view('maintenance.stock_transfers.show', compact('transfer'));
        } catch (Throwable $e) {
            Log::error('StockTransferController@show failed', [
                'stock_transfer_id' => $stock_transfer->id ?? null,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash('Failed to load stock transfer details.')->error();

            return back();
        }
    }

    public function searchProducts(Request $request)
    {
        try {
            $search = trim((string) $request->get('q', ''));
            $locationId = (int) $request->get('from_location_id');

            $excludeIds = collect($request->get('exclude_ids', []))
                ->flatten()
                ->map(function ($id) {
                    return is_numeric($id) ? (int) $id : null;
                })
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (strlen($search) < 2 || ! $locationId) {
                return response()->json([]);
            }

            $location = Location::where('id', $locationId)
                ->where('is_active', true)
                ->first();

            if (! $location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected source location is invalid or inactive.',
                ], 422);
            }

            $products = Product::query()
                ->with(['category', 'stocks'])
                ->when(! empty($excludeIds), function ($query) use ($excludeIds) {
                    $query->whereNotIn('id', $excludeIds);
                })
                ->where(function ($query) use ($search) {
                    $query->where('product_name', 'like', "%{$search}%")
                        ->orWhere('part_number', 'like', "%{$search}%")
                        ->orWhere('supplier_name', 'like', "%{$search}%")
                        ->orWhere('details', 'like', "%{$search}%");
                })
                ->whereHas('stocks', function ($q) use ($locationId) {
                    $q->where('location_id', $locationId)
                        ->where('qty', '>', 0);
                })
                ->orderBy('product_name')
                ->take(20)
                ->get()
                ->map(function ($product) use ($locationId) {
                    $stock = $product->stocks->where('location_id', (int) $locationId)->first();

                    return [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'supplier_name' => $product->supplier_name,
                        'category' => optional($product->category)->name,
                        'unit' => $product->unit,
                        'part_number' => $product->part_number,
                        'details' => $product->details,
                        'stock' => $stock ? (int) $stock->qty : 0,
                    ];
                })
                ->values();

            return response()->json($products);
        } catch (Throwable $e) {
            Log::error('StockTransferController@searchProducts failed', [
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
