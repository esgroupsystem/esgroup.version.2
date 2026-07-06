<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReceivingController extends Controller
{
    public function index(Request $request): View|string
    {
        $search = trim((string) $request->search);

        $receivings = Receiving::query()
            ->with(['location', 'receiver'])
            ->withCount('items');

        $this->restrictLocation($receivings);

        $receivings = $receivings
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $q) use ($search): void {
                    $q->where('receiving_number', 'like', "%{$search}%")
                        ->orWhere('delivered_by', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhereHas('location', function (Builder $locationQuery) use ($search): void {
                            $locationQuery->where('name', 'like', "%{$search}%");
                        });
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

    public function create(): View
    {
        $products = Product::query()
            ->with('category')
            ->select([
                'id',
                'category_id',
                'product_name',
                'supplier_name',
                'unit',
                'part_number',
                'details',
                'stock_qty',
            ])
            ->orderBy('product_name')
            ->get();

        $locationId = $this->userLocationId();

        $locations = Location::query()
            ->where('is_active', 1)
            ->when($locationId, function (Builder $query) use ($locationId): void {
                $query->where('id', $locationId);
            })
            ->orderBy('name')
            ->get();

        return view('maintenance.receive.create', compact('products', 'locations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'delivered_by' => ['required', 'string', 'max:255'],
            'delivery_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'proof_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'integer', 'distinct', 'exists:products,id'],

            'qty_delivered' => ['required', 'array', 'min:1'],
            'qty_delivered.*' => ['required', 'integer', 'min:1'],
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

        try {
            DB::transaction(function () use ($request, $validated, &$proofPath): void {
                if ($request->hasFile('proof_image')) {
                    $proofPath = $request->file('proof_image')->store('receiving_proofs', 'public');
                }

                $receiving = Receiving::query()->create([
                    'receiving_number' => 'PENDING-'.Str::uuid(),
                    'location_id' => $validated['location_id'],
                    'delivered_by' => $validated['delivered_by'],
                    'delivery_date' => $validated['delivery_date'],
                    'remarks' => $validated['remarks'] ?? null,
                    'proof_image' => $proofPath,
                    'received_by' => Auth::id(),
                ]);

                $receiving->update([
                    'receiving_number' => $this->generateReceivingNumber($receiving),
                ]);

                foreach ($validated['product_id'] as $index => $productId) {
                    $qty = (int) $validated['qty_delivered'][$index];

                    $product = Product::query()
                        ->whereKey($productId)
                        ->lockForUpdate()
                        ->firstOrFail();

                    ReceivingItem::query()->create([
                        'receiving_id' => $receiving->id,
                        'product_id' => $product->id,
                        'qty_delivered' => $qty,
                        'qty_rolled_back' => 0,
                    ]);

                    $productStock = $this->stockRowForUpdate(
                        productId: (int) $product->id,
                        locationId: (int) $validated['location_id']
                    );

                    $productStock->increment('qty', $qty);

                    $this->syncProductTotalStock((int) $product->id);
                }
            }, 3);

            return redirect()
                ->route('receivings.index')
                ->with('success', 'Receiving saved successfully. Stock quantities were updated.');
        } catch (\Throwable $e) {
            if ($proofPath && Storage::disk('public')->exists($proofPath)) {
                Storage::disk('public')->delete($proofPath);
            }

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(int $receiving): View
    {
        $receiving = Receiving::query()
            ->with(['receiver', 'items.product', 'location'])
            ->findOrFail($receiving);

        $locationId = $this->userLocationId();

        if ($locationId && (int) $receiving->location_id !== (int) $locationId) {
            abort(403, 'You are not allowed to view this receiving record.');
        }

        return view('maintenance.receive.show', compact('receiving'));
    }

    public function searchProducts(Request $request): JsonResponse
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

        $products = Product::query()
            ->with('category')
            ->where(function (Builder $query) use ($search): void {
                $query->where('product_name', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%");
            })
            ->when(! empty($excludeIds), function (Builder $query) use ($excludeIds): void {
                $query->whereNotIn('id', $excludeIds);
            })
            ->orderBy('product_name')
            ->limit(20)
            ->get();

        return response()->json(
            $products->map(function (Product $product): array {
                return [
                    'id' => $product->id,
                    'name' => $product->product_name,
                    'supplier_name' => $product->supplier_name,
                    'unit' => $product->unit,
                    'part_number' => $product->part_number,
                    'stock' => (int) $product->stock_qty,
                    'category' => optional($product->category)->name,
                ];
            })->values()
        );
    }

    public function rollbackItem(Request $request, int $receiving, int $item): RedirectResponse
    {
        abort_unless(
            $request->user()->can('receivings.rollback'),
            403,
            'You are not authorized to rollback receiving items.'
        );

        $validated = $request->validate([
            'rollback_qty' => ['required', 'integer', 'min:1'],
        ]);

        $productName = 'Selected product';
        $rollbackQty = (int) $validated['rollback_qty'];

        try {
            DB::transaction(function () use ($validated, $receiving, $item, &$productName, &$rollbackQty): void {
                $receivingRecord = Receiving::query()
                    ->with('location')
                    ->whereKey($receiving)
                    ->lockForUpdate()
                    ->firstOrFail();

                $locationId = $this->userLocationId();

                if ($locationId && (int) $receivingRecord->location_id !== (int) $locationId) {
                    throw new \RuntimeException('You are not allowed to rollback this receiving record.');
                }

                $receivingItem = ReceivingItem::query()
                    ->with('product')
                    ->where('receiving_id', $receivingRecord->id)
                    ->whereKey($item)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $receivingItem->product) {
                    throw new \RuntimeException('The selected product no longer exists.');
                }

                $productName = $receivingItem->product->product_name ?? 'Selected product';
                $rollbackQty = (int) $validated['rollback_qty'];

                $alreadyRolledBack = (int) ($receivingItem->qty_rolled_back ?? 0);
                $deliveredQty = (int) $receivingItem->qty_delivered;
                $remainingQty = $deliveredQty - $alreadyRolledBack;

                if ($remainingQty <= 0) {
                    throw new \RuntimeException("{$productName} is already fully rolled back.");
                }

                if ($rollbackQty > $remainingQty) {
                    throw new \RuntimeException(
                        "Rollback quantity exceeds remaining quantity for {$productName}. Remaining quantity: {$remainingQty}."
                    );
                }

                $productStock = ProductStock::query()
                    ->where('product_id', $receivingItem->product_id)
                    ->where('location_id', $receivingRecord->location_id)
                    ->lockForUpdate()
                    ->first();

                if (! $productStock) {
                    throw new \RuntimeException(
                        "No stock record found for {$productName} in {$receivingRecord->location->name}."
                    );
                }

                if ((int) $productStock->qty < $rollbackQty) {
                    throw new \RuntimeException(
                        "Current stock is lower than rollback quantity for {$productName}. Available stock: {$productStock->qty}."
                    );
                }

                $productStock->decrement('qty', $rollbackQty);

                $receivingItem->update([
                    'qty_rolled_back' => $alreadyRolledBack + $rollbackQty,
                    'last_rolled_back_at' => now(),
                ]);

                $this->syncProductTotalStock((int) $receivingItem->product_id);
            }, 3);
        } catch (\Throwable $e) {
            return redirect()
                ->route('receivings.show', $receiving)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('receivings.show', $receiving)
            ->with('success', "{$productName} rollback completed. Quantity rolled back: {$rollbackQty}.");
    }

    private function stockRowForUpdate(int $productId, int $locationId): ProductStock
    {
        $productStock = ProductStock::query()
            ->where('product_id', $productId)
            ->where('location_id', $locationId)
            ->lockForUpdate()
            ->first();

        if ($productStock) {
            return $productStock;
        }

        return ProductStock::query()->create([
            'product_id' => $productId,
            'location_id' => $locationId,
            'qty' => 0,
        ]);
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

    private function generateReceivingNumber(Receiving $receiving): string
    {
        return 'RCV-'.now()->format('Y').'-'.str_pad((string) $receiving->id, 5, '0', STR_PAD_LEFT);
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
