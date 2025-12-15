<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseReceive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseReceiveController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $pos = PurchaseOrder::with(['requester', 'items'])
            ->when($search, function ($query) use ($search) {
                $query->where('po_number', 'like', "%$search%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        if ($request->ajax()) {
            return view('maintenance.receive.table', compact('pos'))->render();
        }

        return view('maintenance.receive.index', compact('pos', 'search'));
    }

    public function receive(Request $request, $id)
    {
        $request->validate([
            'received_qty' => 'required|integer|min:1',
        ]);

        $item = PurchaseOrderItem::with(['product', 'purchaseOrder'])->findOrFail($id);

        if ($item->received_qty + $request->received_qty > $item->purchased_qty) {
            return back()->with('error', 'Received quantity exceeds purchased quantity.');
        }

        $item->received_qty += $request->received_qty;
        $item->save();

        $item->product->increment('stock_qty', $request->received_qty);

        PurchaseReceive::create([
            'purchase_order_item_id' => $item->id,
            'qty_received' => $request->received_qty,
            'received_by' => Auth::id(),
        ]);

        $po = $item->purchaseOrder->load('items');

        $totalPurchased = $po->items->sum('purchased_qty');
        $totalReceived = $po->items->sum('received_qty');

        if ($totalReceived == 0) {
            $po->status = 'Pending';
        } elseif ($totalReceived < $totalPurchased) {
            $po->status = 'Partially Received';
        } else {
            $po->status = 'Received';
        }

        $po->save();

        return back()->with('success', 'Item received successfully.');
    }

    public function details($id)
    {
        $po = PurchaseOrder::with(['requester', 'items.product'])->findOrFail($id);

        return view('maintenance.receive.modal_content', compact('po'));
    }
}
