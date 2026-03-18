<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceivingController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

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

        return view('maintenance.receive.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'delivered_by' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'remarks' => 'nullable|string',
            'proof_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'product_id' => 'required|array|min:1',
            'product_id.*' => 'required|distinct|exists:products,id',

            'qty_delivered' => 'required|array|min:1',
            'qty_delivered.*' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $proofPath = null;

            if ($request->hasFile('proof_image')) {
                $proofPath = $request->file('proof_image')->store('receiving_proofs', 'public');
            }

            $receiving = Receiving::create([
                'receiving_number' => 'TEMP',
                'delivered_by' => $request->delivered_by,
                'delivery_date' => $request->delivery_date,
                'remarks' => $request->remarks,
                'proof_image' => $proofPath,
                'received_by' => Auth::id(),
            ]);

            $receiving->update([
                'receiving_number' => 'RCV-'.date('Y').'-'.str_pad($receiving->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($request->product_id as $index => $productId) {
                $qty = (int) ($request->qty_delivered[$index] ?? 0);

                if ($qty <= 0) {
                    continue;
                }

                ReceivingItem::create([
                    'receiving_id' => $receiving->id,
                    'product_id' => $productId,
                    'qty_delivered' => $qty,
                ]);

                Product::where('id', $productId)->increment('stock_qty', $qty);
            }
        });

        return redirect()
            ->route('receivings.index')
            ->with('success', 'Receiving saved successfully, proof uploaded, and stocks updated.');
    }

    public function show($id)
    {
        $receiving = Receiving::with(['receiver', 'items.product'])->findOrFail($id);

        return view('maintenance.receive.show', compact('receiving'));
    }

    private function generateReceivingNumber()
    {
        $lastId = Receiving::max('id') ?? 0;
        $nextId = $lastId + 1;

        return 'RCV-'.date('Y').'-'.str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }
}
