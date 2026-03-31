<?php

namespace App\Http\Controllers\IT_Department;

use App\Http\Controllers\Controller;
use App\Models\BusDetail;
use App\Models\CctvConcern;
use App\Models\CctvConcernItem;
use App\Models\ItInventoryItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CctvController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = $request->get('status');

        $baseQuery = CctvConcern::query()
            ->with([
                'assignee:id,full_name',
                'usedItems.inventoryItem:id,item_name,unit',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($x) use ($q) {
                    $x->where('jo_no', 'like', "%{$q}%")
                        ->orWhere('bus_no', 'like', "%{$q}%")
                        ->orWhere('reported_by', 'like', "%{$q}%")
                        ->orWhere('problem_details', 'like', "%{$q}%");
                });
            })
            ->when(! empty($status), fn ($query) => $query->where('status', $status));

        $jobOrders = (clone $baseQuery)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $allJobOrders = (clone $baseQuery)->get();

        $agents = User::orderBy('full_name')->get(['id', 'full_name']);

        $buses = BusDetail::orderBy('body_number')
            ->get(['id', 'garage', 'name', 'body_number', 'plate_number']);

        $inventoryItems = ItInventoryItem::query()
            ->where('is_active', true)
            ->orderBy('item_name')
            ->get([
                'id',
                'item_name',
                'category',
                'brand',
                'model',
                'unit',
                'stock_qty',
                'location',
            ]);

        return view('it_department.concern.index', compact(
            'jobOrders',
            'allJobOrders',
            'agents',
            'buses',
            'inventoryItems'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bus_no' => 'required|string|max:50',
            'issue_type' => 'required|string|max:80',
            'problem_details' => 'required|string',
            'status' => 'required|string|max:30',
            'assigned_to' => 'nullable|exists:users,id',

            'items' => 'nullable|array',
            'items.*.it_inventory_item_id' => 'nullable|exists:it_inventory_items,id',
            'items.*.qty_used' => 'nullable|integer|min:1',
            'items.*.remarks' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $data['reported_by'] = auth()->user()->full_name;
            $data['created_by'] = auth()->id();

            $year = now()->year;
            $last = CctvConcern::where('jo_no', 'like', "JO-$year-%")->latest('id')->first();
            $next = $last ? intval(substr($last->jo_no, -5)) + 1 : 1;
            $data['jo_no'] = "JO-$year-".str_pad($next, 5, '0', STR_PAD_LEFT);

            unset($data['items']);

            $jobOrder = CctvConcern::create($data);

            $items = $request->input('items', []);
            $savedItems = [];
            $usedItemNames = [];

            foreach ($items as $row) {
                $inventoryId = (int) ($row['it_inventory_item_id'] ?? 0);
                $qtyUsed = (int) ($row['qty_used'] ?? 0);
                $remarks = $row['remarks'] ?? null;

                if (! $inventoryId || $qtyUsed <= 0) {
                    continue;
                }

                $inventory = ItInventoryItem::lockForUpdate()->findOrFail($inventoryId);

                if ((int) $inventory->stock_qty < $qtyUsed) {
                    throw new \Exception("Not enough stock for item: {$inventory->item_name}");
                }

                $inventory->decrement('stock_qty', $qtyUsed);

                $savedItems[] = new CctvConcernItem([
                    'it_inventory_item_id' => $inventory->id,
                    'qty_used' => $qtyUsed,
                    'remarks' => $remarks,
                ]);

                $usedItemNames[] = $inventory->item_name.' x'.$qtyUsed;
            }

            if (! empty($savedItems)) {
                $jobOrder->usedItems()->saveMany($savedItems);
            }

            $jobOrder->update([
                'cctv_part' => ! empty($usedItemNames) ? implode(', ', $usedItemNames) : null,
            ]);

            DB::commit();

            return redirect()
                ->route('concern.cctv.index')
                ->with('success', 'CCTV Job Order created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('concern.cctv.index')
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $jobOrder = CctvConcern::with('usedItems')->findOrFail($id);

        $data = $request->validate([
            'bus_no' => ['required', 'string', 'max:50'],
            'issue_type' => ['required', 'string', 'max:80'],
            'problem_details' => ['required', 'string'],
            'action_taken' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:30'],
            'assigned_to' => ['nullable', 'exists:users,id'],

            'items' => 'nullable|array',
            'items.*.it_inventory_item_id' => 'nullable|exists:it_inventory_items,id',
            'items.*.qty_used' => 'nullable|integer|min:1',
            'items.*.remarks' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            foreach ($jobOrder->usedItems as $oldItem) {
                $inventory = ItInventoryItem::lockForUpdate()->find($oldItem->it_inventory_item_id);
                if ($inventory) {
                    $inventory->increment('stock_qty', (int) $oldItem->qty_used);
                }
            }

            $jobOrder->usedItems()->delete();

            if (in_array($data['status'], ['Fixed', 'Closed']) && ! $jobOrder->fixed_at) {
                $data['fixed_at'] = now();
            }

            if (! in_array($data['status'], ['Fixed', 'Closed'])) {
                $data['fixed_at'] = null;
            }

            unset($data['items']);
            $jobOrder->update($data);

            $items = $request->input('items', []);
            $savedItems = [];
            $usedItemNames = [];

            foreach ($items as $row) {
                $inventoryId = (int) ($row['it_inventory_item_id'] ?? 0);
                $qtyUsed = (int) ($row['qty_used'] ?? 0);
                $remarks = $row['remarks'] ?? null;

                if (! $inventoryId || $qtyUsed <= 0) {
                    continue;
                }

                $inventory = ItInventoryItem::lockForUpdate()->findOrFail($inventoryId);

                if ((int) $inventory->stock_qty < $qtyUsed) {
                    throw new \Exception("Not enough stock for item: {$inventory->item_name}");
                }

                $inventory->decrement('stock_qty', $qtyUsed);

                $savedItems[] = new CctvConcernItem([
                    'it_inventory_item_id' => $inventory->id,
                    'qty_used' => $qtyUsed,
                    'remarks' => $remarks,
                ]);

                $usedItemNames[] = $inventory->item_name.' x'.$qtyUsed;
            }

            if (! empty($savedItems)) {
                $jobOrder->usedItems()->saveMany($savedItems);
            }

            $jobOrder->update([
                'cctv_part' => ! empty($usedItemNames) ? implode(', ', $usedItemNames) : null,
            ]);

            DB::commit();

            return redirect()
                ->route('concern.cctv.index')
                ->with('success', 'Job Order updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('concern.cctv.index')
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $jobOrder = CctvConcern::with('usedItems')->findOrFail($id);

            foreach ($jobOrder->usedItems as $usedItem) {
                $inventory = ItInventoryItem::lockForUpdate()->find($usedItem->it_inventory_item_id);
                if ($inventory) {
                    $inventory->increment('stock_qty', (int) $usedItem->qty_used);
                }
            }

            $jobOrder->delete();

            DB::commit();

            return redirect()
                ->route('concern.cctv.index')
                ->with('success', 'Job Order deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('concern.cctv.index')
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function view($id)
    {
        $jobOrder = CctvConcern::with([
            'assignee',
            'creator',
            'usedItems.inventoryItem',
        ])->findOrFail($id);

        return view('it_department.cctv_concern', compact('jobOrder'));
    }

    public function acceptTask($id)
    {
        return back();
    }

    public function markAsDone($id)
    {
        return back();
    }

    public function addNote(Request $request, $id)
    {
        return back();
    }

    public function addFiles(Request $request, $id)
    {
        return back();
    }

    public function export($type)
    {
        //
    }
}
