<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReceivingController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->search);

        $receivings = Receiving::with(['location', 'receiver'])
            ->withCount('items');

        $this->restrictLocation($receivings);

        $receivings = $receivings
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('receiving_number', 'like', "%{$search}%")
                        ->orWhere('delivered_by', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('maintenance.receive.table', compact('receivings'))->render();
        }

        return view('maintenance.receive.index', compact('receivings', 'search'));
    }

    public function create()
    {
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

        $locationId = $this->userLocationId();

        $locations = Location::where('is_active', 1)
            ->when($locationId, function ($query) use ($locationId) {
                $query->where('id', $locationId);
            })
            ->orderBy('name')
            ->get();

        return view('maintenance.receive.create', compact('products', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'delivered_by' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'remarks' => 'nullable|string',
            'proof_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|integer|distinct|exists:products,id',

            'qty_delivered' => 'required|array|min:1',
            'qty_delivered.*' => 'required|integer|min:1',
        ]);

        if (count($validated['product_id']) !== count($validated['qty_delivered'])) {
            return back()
                ->withInput()
                ->with('error', 'Product count and quantity count do not match.');
        }

        $locationId = $this->userLocationId();

        if ($locationId && (int) $validated['location_id'] !== (int) $locationId) {
            return back()
                ->withInput()
                ->with('error', 'You are only allowed to receive items for your assigned garage.');
        }

        $proofPath = null;

        DB::beginTransaction();

        try {
            if ($request->hasFile('proof_image')) {
                $proofPath = $request->file('proof_image')->store('receiving_proofs', 'public');
            }

            $receiving = Receiving::create([
                'receiving_number' => 'TEMP',
                'location_id' => $validated['location_id'],
                'delivered_by' => $validated['delivered_by'],
                'delivery_date' => $validated['delivery_date'],
                'remarks' => $validated['remarks'] ?? null,
                'proof_image' => $proofPath,
                'received_by' => Auth::id(),
            ]);

            $receiving->update([
                'receiving_number' => 'RCV-'.date('Y').'-'.str_pad($receiving->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($validated['product_id'] as $index => $productId) {
                $qty = (int) $validated['qty_delivered'][$index];

                $product = Product::lockForUpdate()->findOrFail($productId);

                ReceivingItem::create([
                    'receiving_id' => $receiving->id,
                    'product_id' => $product->id,
                    'qty_delivered' => $qty,
                ]);

                $productStock = ProductStock::lockForUpdate()->firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'location_id' => $validated['location_id'],
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
                ->with('success', 'Receiving saved successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($proofPath && Storage::disk('public')->exists($proofPath)) {
                Storage::disk('public')->delete($proofPath);
            }

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $receiving = Receiving::with(['receiver', 'items.product', 'location'])
            ->findOrFail($id);

        $locationId = $this->userLocationId();

        if ($locationId && (int) $receiving->location_id !== (int) $locationId) {
            abort(403, 'You are not allowed to view this receiving record.');
        }

        return view('maintenance.receive.show', compact('receiving'));
    }

    public function searchProducts(Request $request)
    {
        $search = trim((string) $request->search);

        if ($search === '') {
            return response()->json([]);
        }

        $excludeIds = collect(explode(',', (string) $request->exclude_ids))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $products = Product::with('category')
            ->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%");
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
    }

    public function rollbackItem(Request $request, $receivingId, $itemId)
    {
        $userRole = Auth::user()->role ?? Auth::user()->user_role ?? null;

        if (! in_array($userRole, ['Developer', 'Maintenance Engineer'])) {
            return back()->with('error', 'You are not authorized to rollback receiving items.');
        }

        $validated = $request->validate([
            'rollback_qty' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated, $receivingId, $itemId) {
            $receiving = Receiving::lockForUpdate()
                ->with('location')
                ->findOrFail($receivingId);

            $locationId = $this->userLocationId();

            if ($locationId && (int) $receiving->location_id !== (int) $locationId) {
                abort(403, 'You are not allowed to rollback this receiving record.');
            }

            $item = ReceivingItem::lockForUpdate()
                ->with('product')
                ->where('receiving_id', $receiving->id)
                ->findOrFail($itemId);

            $rollbackQty = (int) $validated['rollback_qty'];
            $alreadyRolledBack = (int) ($item->qty_rolled_back ?? 0);
            $remainingQty = (int) $item->qty_delivered - $alreadyRolledBack;

            if ($rollbackQty > $remainingQty) {
                abort(422, 'Rollback quantity exceeds remaining received quantity.');
            }

            $productStock = ProductStock::lockForUpdate()
                ->where('product_id', $item->product_id)
                ->where('location_id', $receiving->location_id)
                ->firstOrFail();

            if ((int) $productStock->qty < $rollbackQty) {
                abort(422, 'Current stock is lower than rollback quantity.');
            }

            $productStock->decrement('qty', $rollbackQty);

            $item->update([
                'qty_rolled_back' => $alreadyRolledBack + $rollbackQty,
                'last_rolled_back_at' => now(),
            ]);

            $item->product->update([
                'stock_qty' => ProductStock::where('product_id', $item->product_id)->sum('qty'),
            ]);
        });

        return redirect()
            ->route('receivings.show', $receivingId)
            ->with('success', 'Receiving item rolled back successfully.');
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
