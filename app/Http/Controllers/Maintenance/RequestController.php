<?php

namespace App\Http\Controllers\Maintenance;

use App\Helpers\Notifier;
use App\Http\Controllers\Controller;
use App\Mail\POCreatedMail;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['items.product', 'requester'])->orderBy('id', 'desc');

        // AJAX SEARCH
        if ($request->ajax()) {
            if ($request->search) {
                $query->whereHas('requester', function ($q) use ($request) {
                    $q->where('full_name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                })
                    ->orWhere('po_number', 'like', "%{$request->search}%")
                    ->orWhere('garage', 'like', "%{$request->search}%");
            }

            $orders = $query->paginate(10);

            return view('maintenance.request.table', compact('orders'))->render();
        }

        // FULL LOAD (non-AJAX)
        $orders = $query->paginate(10);

        return view('maintenance.request.index', compact('orders'));
    }

    public function create()
    {
        return view('maintenance.request.store', [
            'po_number' => 'PO-'.str_pad(PurchaseOrder::count() + 1, 5, '0', STR_PAD_LEFT),
            'categories' => Category::all(),
            'products' => Product::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'garage' => ['required'],
            'category_id.*' => ['required'],
            'product_id.*' => ['required'],
            'qty.*' => ['required', 'numeric', 'min:1'],
        ]);

        $po = PurchaseOrder::create([
            'po_number' => $request->po_number ?? 'PO-'.time(),
            'garage' => $request->garage,
            'requester_id' => Auth::id(),
            'status' => 'Pending',
        ]);

        foreach ($request->product_id as $index => $product_id) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id' => $product_id,
                'qty' => $request->qty[$index],
            ]);
        }

        Notifier::notifyRoles(
            ['Maintenance Staff', 'Maintenance Engineer'],
            new POCreatedMail($po)
        );

        flash('Purchase Order Created Successfully!')->success();

        return redirect()->route('request.index');
    }

    public function update(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $po->status = $request->status;
        $po->save();

        flash('Status updated successfully!')->success();

        return back();
    }
}
