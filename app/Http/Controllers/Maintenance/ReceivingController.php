<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReceivingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = trim((string) $request->search);

            $receivings = Receiving::with(['receiver', 'items.product'])
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('receiving_number', 'like', "%{$search}%")
                            ->orWhere('delivered_by', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(10)
                ->withQueryString();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'html' => view('maintenance.receive.table', compact('receivings'))->render(),
                ]);
            }

            return view('maintenance.receive.index', compact('receivings', 'search'));
        } catch (Throwable $e) {
            Log::error('ReceivingController@index failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load receiving records.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            flash('Failed to load receiving records. Please check the logs.')->error();

            return back();
        }
    }

    public function create()
    {
        try {
            $products = Product::with('category')
                ->select(
                    'id',
                    'category_id',
                    'product_name',
                    'supplier_name',
                    'unit',
                    'part_number',
                    'details',
                    'stock_qty'
                )
                ->orderBy('product_name')
                ->get();

            return view('maintenance.receive.create', compact('products'));
        } catch (Throwable $e) {
            Log::error('ReceivingController@create failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash('Failed to load receiving form.')->error();

            return back();
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'delivered_by' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'remarks' => 'nullable|string',
            'proof_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|integer|distinct|exists:products,id',

            'qty_delivered' => 'required|array|min:1',
            'qty_delivered.*' => 'required|integer|min:1',
        ], [
            'delivered_by.required' => 'Delivered by is required.',
            'delivered_by.max' => 'Delivered by must not exceed 255 characters.',
            'delivery_date.required' => 'Delivery date is required.',
            'delivery_date.date' => 'Delivery date must be a valid date.',
            'proof_image.image' => 'Proof file must be an image.',
            'proof_image.mimes' => 'Proof image must be JPG, JPEG, or PNG only.',
            'proof_image.max' => 'Proof image must not exceed 2MB.',
            'product_id.required' => 'At least one product is required.',
            'product_id.array' => 'Product list format is invalid.',
            'product_id.min' => 'Please add at least one product.',
            'product_id.*.distinct' => 'Duplicate products are not allowed.',
            'product_id.*.exists' => 'One of the selected products does not exist.',
            'qty_delivered.required' => 'Quantity delivered is required.',
            'qty_delivered.array' => 'Quantity list format is invalid.',
            'qty_delivered.*.integer' => 'Quantity delivered must be a whole number.',
            'qty_delivered.*.min' => 'Quantity delivered must be at least 1.',
        ]);

        if (count($validated['product_id']) !== count($validated['qty_delivered'])) {
            return back()
                ->withInput()
                ->with('error', 'Product count and quantity count do not match.');
        }

        $proofPath = null;

        try {
            DB::beginTransaction();

            if ($request->hasFile('proof_image')) {
                $proofPath = $request->file('proof_image')->store('receiving_proofs', 'public');

                if (! $proofPath) {
                    throw new \Exception('Proof image upload failed.');
                }
            }

            $defaultLocation = Location::where('name', 'Main Office')->first();

            if (! $defaultLocation) {
                throw new \Exception('Default receiving location "Main Office" was not found. Please create it first or update the controller location name.');
            }

            $receiving = Receiving::create([
                'receiving_number' => 'TEMP',
                'delivered_by' => $validated['delivered_by'],
                'delivery_date' => $validated['delivery_date'],
                'remarks' => $validated['remarks'] ?? null,
                'proof_image' => $proofPath,
                'received_by' => Auth::id(),
            ]);

            $receivingNumber = 'RCV-'.date('Y').'-'.str_pad($receiving->id, 5, '0', STR_PAD_LEFT);

            $receiving->update([
                'receiving_number' => $receivingNumber,
            ]);

            foreach ($validated['product_id'] as $index => $productId) {
                $qty = (int) ($validated['qty_delivered'][$index] ?? 0);

                if ($qty <= 0) {
                    throw new \Exception('Quantity delivered must be greater than zero for row '.($index + 1).'.');
                }

                $product = Product::lockForUpdate()->find($productId);

                if (! $product) {
                    throw new ModelNotFoundException('Product not found for row '.($index + 1).'.');
                }

                ReceivingItem::create([
                    'receiving_id' => $receiving->id,
                    'product_id' => $productId,
                    'qty_delivered' => $qty,
                ]);

                $productStock = ProductStock::lockForUpdate()->firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'location_id' => $defaultLocation->id,
                    ],
                    [
                        'qty' => 0,
                    ]
                );

                $productStock->increment('qty', $qty);

                $product->update([
                    'stock_qty' => ProductStock::where('product_id', $product->id)->sum('qty'),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('receivings.index')
                ->with('success', 'Receiving saved successfully, stock added to Main Office, and total stock updated.');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            if ($proofPath && Storage::disk('public')->exists($proofPath)) {
                Storage::disk('public')->delete($proofPath);
            }

            Log::warning('ReceivingController@store model not found', [
                'message' => $e->getMessage(),
                'request' => $request->all(),
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'One of the selected products was not found. Please refresh and try again.');
        } catch (QueryException $e) {
            DB::rollBack();

            if ($proofPath && Storage::disk('public')->exists($proofPath)) {
                Storage::disk('public')->delete($proofPath);
            }

            Log::error('ReceivingController@store database error', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'request' => $request->all(),
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Database error while saving receiving transaction. Please check the logs.');
        } catch (Throwable $e) {
            DB::rollBack();

            if ($proofPath && Storage::disk('public')->exists($proofPath)) {
                Storage::disk('public')->delete($proofPath);
            }

            Log::error('ReceivingController@store failed', [
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

    public function show($id)
    {
        try {
            $receiving = Receiving::with(['receiver', 'items.product'])->findOrFail($id);

            return view('maintenance.receive.show', compact('receiving'));
        } catch (ModelNotFoundException $e) {
            Log::warning('ReceivingController@show not found', [
                'receiving_id' => $id,
            ]);

            flash('Receiving record not found.')->error();

            return back();
        } catch (Throwable $e) {
            Log::error('ReceivingController@show failed', [
                'receiving_id' => $id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            flash('Failed to load receiving details.')->error();

            return back();
        }
    }

    private function generateReceivingNumber()
    {
        try {
            $lastId = Receiving::max('id') ?? 0;
            $nextId = $lastId + 1;

            return 'RCV-'.date('Y').'-'.str_pad($nextId, 5, '0', STR_PAD_LEFT);
        } catch (Throwable $e) {
            Log::error('ReceivingController@generateReceivingNumber failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return 'RCV-'.date('Y').'-ERROR';
        }
    }

    public function searchProducts(Request $request)
    {
        try {
            $search = trim((string) ($request->search ?? ''));

            if ($search === '') {
                return response()->json([]);
            }

            $excludeIds = collect(explode(',', (string) ($request->exclude_ids ?? '')))
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $products = Product::with('category')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('product_name', 'like', "%{$search}%")
                            ->orWhere('supplier_name', 'like', "%{$search}%")
                            ->orWhere('unit', 'like', "%{$search}%")
                            ->orWhere('part_number', 'like', "%{$search}%")
                            ->orWhere('details', 'like', "%{$search}%");
                    });
                })
                ->when(! empty($excludeIds), function ($query) use ($excludeIds) {
                    $query->whereNotIn('id', $excludeIds);
                })
                ->orderBy('product_name')
                ->limit(20)
                ->get();

            return response()->json(
                $products->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'name' => $p->product_name,
                        'supplier_name' => $p->supplier_name,
                        'unit' => $p->unit,
                        'part_number' => $p->part_number,
                        'stock' => (int) $p->stock_qty,
                        'category' => optional($p->category)->name,
                    ];
                })->values()
            );
        } catch (Throwable $e) {
            Log::error('ReceivingController@searchProducts failed', [
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
