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
        $order = PurchaseOrder::with('items')->findOrFail($order);

        try {
            DB::beginTransaction();

            // Ensure items exist
            if (! $request->has('items') || ! is_array($request->items)) {
                throw new \Exception('No items were submitted.');
            }

            foreach ($request->items as $itemId => $data) {

                $item = $order->items()->find($itemId);
                if (! $item) {
                    Log::warning('Item not found in PO update', [
                        'order_id' => $order->id,
                        'missing_item_id' => $itemId,
                    ]);

                    continue;
                }

                // REMOVE ITEM
                if (isset($data['remove']) && $data['remove'] == 1) {
                    $item->update([
                        'purchased_qty' => 0,
                        'store_name' => $data['store_name'] ?? null,
                        'removed' => true,
                    ]);

                    Log::info('Item removed by accounting.', [
                        'order_id' => $order->id,
                        'item_id' => $item->id,
                        'store_name' => $data['store_name'] ?? null,
                    ]);

                    continue;
                }

                // VALID PURCHASED QTY
                $purchased = max(0, min($item->qty, (int) ($data['purchased_qty'] ?? 0)));

                $item->update([
                    'purchased_qty' => $purchased,
                    'store_name' => $data['store_name'] ?? null,
                    'removed' => false,
                ]);

                Log::info('Accounting updated item purchase details.', [
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'purchased_qty' => $purchased,
                    'store_name' => $data['store_name'] ?? null,
                ]);
            }

            // UPDATE PO STATUS
            $oldStatus = $order->status;
            $order->status = $request->status;
            $order->save();

            Log::info('PO status updated by accounting.', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
            ]);

            DB::commit();

            return back()->with('success', 'Accounting update saved successfully!');

        } catch (\Throwable $e) {

            DB::rollBack();

            // STORE ERROR INTO LARAVEL LOGS
            Log::error('Accounting PO update failed', [
                'order_id' => $order->id,
                'error_message' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return back()->with('error', 'Update failed: '.$e->getMessage());
        }
    }
}
