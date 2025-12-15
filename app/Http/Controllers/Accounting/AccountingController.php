<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountingController extends Controller
{
    // Show ONLY Approved POs
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('requester')
            ->whereIn('status', [
                'Approved',
                'Partial Order',
                'For Delivery',
                'Delivered',
            ])
            ->orderBy('id', 'desc');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('po_number', 'like', "%{$request->search}%")
                    ->orWhereHas('requester', function ($sub) use ($request) {
                        $sub->where('full_name', 'like', "%{$request->search}%");
                    });
            });
        }

        $orders = $query->paginate(10);

        return view('accounting.index', compact('orders'));
    }

    public function update(Request $request, $order)
    {
        // Always resolve PO model
        if ($order instanceof PurchaseOrder) {
            $order->load('items');
        } else {
            $order = PurchaseOrder::with('items')->findOrFail($order);
        }

        try {
            DB::beginTransaction();

            // Validate items structure
            if (! $request->has('items') || ! is_array($request->items)) {
                throw new \Exception('No valid items were submitted for update.');
            }

            foreach ($request->items as $itemId => $data) {

                $item = $order->items()->find($itemId);

                if (! $item) {
                    continue; // Skip missing items silently
                }

                // REMOVE ITEM
                if (! empty($data['remove']) && $data['remove'] == 1) {
                    $item->update([
                        'purchased_qty' => 0,
                        'store_name' => $data['store_name'] ?? null,
                        'removed' => true,
                    ]);

                    continue;
                }

                // Validate qty
                $purchasedQty = (int) ($data['purchased_qty'] ?? 0);
                $purchased = max(0, min($item->qty, $purchasedQty));

                $item->update([
                    'purchased_qty' => $purchased,
                    'store_name' => $data['store_name'] ?? null,
                    'removed' => false,
                ]);
            }

            // Update PO status
            $order->status = $request->status;
            $order->save();

            DB::commit();

            return back()->with('success', 'Accounting update saved successfully!');

        } catch (\Throwable $e) {

            DB::rollBack();

            // ONLY LOG ERROR HERE
            Log::error('PO update failed.', [
                'order_id' => $order->id ?? 'N/A',
                'error_message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Update failed: '.$e->getMessage());
        }
    }
}
