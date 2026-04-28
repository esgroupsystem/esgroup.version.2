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

        $statusOptions = [
            '' => 'All',
            'Open' => 'Open',
            'In Progress' => 'In Progress',
            'Fixed' => 'Fixed',
            'Closed' => 'Closed',
        ];

        $statusClasses = [
            'Open' => 'badge-subtle-warning',
            'In Progress' => 'badge-subtle-info',
            'Fixed' => 'badge-subtle-success',
            'Closed' => 'badge-subtle-secondary',
        ];

        $buses = BusDetail::orderBy('body_number')
            ->get(['id', 'garage', 'name', 'body_number', 'plate_number'])
            ->map(function ($bus) {
                $bus->display_name = collect([
                    $bus->body_number,
                    $bus->plate_number,
                    $bus->name,
                    $bus->garage,
                ])->filter()->implode(' - ');

                return $bus;
            });

        $busDisplayMap = $buses->pluck('display_name', 'body_number');

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
                        ->orWhere('issue_type', 'like', "%{$q}%")
                        ->orWhere('problem_details', 'like', "%{$q}%");
                });
            })
            ->when(! empty($status), fn ($query) => $query->where('status', $status));

        $jobOrders = (clone $baseQuery)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $allJobOrders = (clone $baseQuery)->get();

        $statusCounts = $allJobOrders->groupBy('status')->map->count();

        $totalOrders = $allJobOrders->count();
        $openCount = $statusCounts['Open'] ?? 0;
        $progressCount = $statusCounts['In Progress'] ?? 0;
        $fixedCount = $statusCounts['Fixed'] ?? 0;
        $closedCount = $statusCounts['Closed'] ?? 0;

        $issueCounts = $allJobOrders->groupBy('issue_type')->map->count()->sortDesc();
        $topIssue = $issueCounts->keys()->first();
        $topIssueCount = $issueCounts->first() ?? 0;

        $partCounts = $allJobOrders
            ->flatMap(fn ($job) => $job->usedItems->map(fn ($used) => $used->inventoryItem->item_name ?? null))
            ->filter()
            ->groupBy(fn ($name) => $name)
            ->map->count()
            ->sortDesc();

        $topPart = $partCounts->keys()->first();
        $topPartCount = $partCounts->first() ?? 0;

        $assigneeCounts = $allJobOrders
            ->map(fn ($job) => $job->assignee->full_name ?? null)
            ->filter()
            ->groupBy(fn ($name) => $name)
            ->map->count()
            ->sortDesc();

        $topAssignee = $assigneeCounts->keys()->first();
        $topAssigneeCount = $assigneeCounts->first() ?? 0;

        $agents = User::where('role', 'IT Officer')
            ->orderBy('full_name')
            ->get();

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
            'busDisplayMap',
            'inventoryItems',
            'statusOptions',
            'statusClasses',
            'totalOrders',
            'openCount',
            'progressCount',
            'fixedCount',
            'closedCount',
            'topIssue',
            'topIssueCount',
            'topPart',
            'topPartCount',
            'topAssignee',
            'topAssigneeCount'
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
            $user = $request->user();

            $data['reported_by'] = $user?->full_name ?? 'System';
            $data['created_by'] = $user?->id;

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
            'action_taken' => ['nullable', 'string'],
            'status' => ['required', 'in:Open,In Progress,Fixed,Closed'],
            'assigned_to' => ['nullable', 'exists:users,id'],

            'items' => ['nullable', 'array'],
            'items.*.it_inventory_item_id' => ['nullable', 'exists:it_inventory_items,id'],
            'items.*.qty_used' => ['nullable', 'integer', 'min:1'],
            'items.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::transaction(function () use ($request, $jobOrder, $data) {
                foreach ($jobOrder->usedItems as $oldItem) {
                    $inventory = ItInventoryItem::lockForUpdate()
                        ->find($oldItem->it_inventory_item_id);

                    if ($inventory) {
                        $inventory->increment('stock_qty', (int) $oldItem->qty_used);
                    }
                }

                $jobOrder->usedItems()->delete();

                $data['fixed_at'] = in_array($data['status'], ['Fixed', 'Closed'], true)
                    ? ($jobOrder->fixed_at ?: now())
                    : null;

                unset($data['items']);

                $jobOrder->update($data);

                $savedItems = [];
                $usedItemNames = [];

                foreach ($request->input('items', []) as $row) {
                    $inventoryId = (int) ($row['it_inventory_item_id'] ?? 0);
                    $qtyUsed = (int) ($row['qty_used'] ?? 0);

                    if ($inventoryId <= 0 || $qtyUsed <= 0) {
                        continue;
                    }

                    $inventory = ItInventoryItem::lockForUpdate()->findOrFail($inventoryId);

                    if ((int) $inventory->stock_qty < $qtyUsed) {
                        throw new \RuntimeException("Not enough stock for {$inventory->item_name}.");
                    }

                    $inventory->decrement('stock_qty', $qtyUsed);

                    $savedItems[] = new CctvConcernItem([
                        'it_inventory_item_id' => $inventory->id,
                        'qty_used' => $qtyUsed,
                        'remarks' => $row['remarks'] ?? null,
                    ]);

                    $usedItemNames[] = "{$inventory->item_name} x{$qtyUsed}";
                }

                if ($savedItems) {
                    $jobOrder->usedItems()->saveMany($savedItems);
                }

                $jobOrder->update([
                    'cctv_part' => $usedItemNames ? implode(', ', $usedItemNames) : null,
                ]);
            });

            return back()->with('success', 'Job Order updated.');
        } catch (\Throwable $e) {
            return back()
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

        return view('it_department.concern.index', compact('jobOrder'));
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

    public function busStatus(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $issueColumns = [
            'CCTV' => ['Camera', 'Wiring'],
            'DVR' => ['DVR'],
            'Monitor' => ['Monitor'],
            'Power Supply' => ['Power Supply', 'Power'],
            'Other' => ['Other'],
        ];

        $buses = BusDetail::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($x) use ($q) {
                    $x->where('body_number', 'like', "%{$q}%")
                        ->orWhere('plate_number', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('garage', 'like', "%{$q}%");
                });
            })
            ->orderBy('body_number')
            ->paginate(20)
            ->withQueryString();

        $allConcerns = CctvConcern::query()
            ->with(['assignee:id,full_name', 'usedItems.inventoryItem:id,item_name,unit,brand'])
            ->whereIn('status', ['Open', 'In Progress', 'Fixed', 'Closed'])
            ->get();

        $activeConcerns = $allConcerns->whereIn('status', ['Open', 'In Progress']);
        $concernsByBus = $activeConcerns->groupBy('bus_no');
        $allConcernsByBus = $allConcerns->groupBy('bus_no');

        $collection = $buses->getCollection()
            ->map(function ($bus) use ($concernsByBus, $allConcernsByBus, $issueColumns) {
                $busConcerns = $concernsByBus->get($bus->body_number, collect());
                $allBusConcerns = $allConcernsByBus->get($bus->body_number, collect());

                $bus->display_name = collect([
                    $bus->body_number,
                    $bus->plate_number,
                    $bus->name,
                    $bus->garage,
                ])->filter()->implode(' - ');

                $bus->status_summary = collect($issueColumns)->map(function ($types) use ($busConcerns) {
                    return $busConcerns->whereIn('issue_type', $types)->count();
                });

                $bus->total_issues = $bus->status_summary->sum();
                $bus->completed_count = $allBusConcerns->whereIn('status', ['Fixed', 'Closed'])->count();

                return $bus;
            })
            ->sortByDesc('total_issues')
            ->values();

        $buses->setCollection($collection);

        return view('it_department.concern.bus-status', [
            'busStatuses' => $buses,
            'issueColumns' => $issueColumns,
            'q' => $q,
        ]);
    }

    public function busStatusShow(Request $request, string $bodyNumber)
    {
        $issue = $request->get('issue');
        $status = $request->get('status');

        $issueColumns = [
            'CCTV' => ['Camera', 'Wiring'],
            'DVR' => ['DVR'],
            'Monitor' => ['Monitor'],
            'Power Supply' => ['Power Supply', 'Power'],
            'Other' => ['Other'],
        ];

        $bus = BusDetail::where('body_number', $bodyNumber)->firstOrFail();

        $bus->display_name = collect([
            $bus->body_number,
            $bus->plate_number,
            $bus->name,
            $bus->garage,
        ])->filter()->implode(' - ');

        $allConcernsBase = CctvConcern::query()
            ->with(['assignee:id,full_name', 'usedItems.inventoryItem:id,item_name,unit,brand'])
            ->where('bus_no', $bus->body_number)
            ->whereIn('status', ['Open', 'In Progress', 'Fixed', 'Closed']);

        $allConcerns = (clone $allConcernsBase)->latest()->get();

        $activeBase = (clone $allConcernsBase)->whereIn('status', ['Open', 'In Progress']);
        $completedBase = (clone $allConcernsBase)->whereIn('status', ['Fixed', 'Closed']);

        if ($issue) {
            $activeBase->where('issue_type', $issue);
            $completedBase->where('issue_type', $issue);
        }

        if ($status) {
            $activeBase->where('status', $status);
            $completedBase->where('status', $status);
        }

        $activeJobOrders = $activeBase->latest()->paginate(5, ['*'], 'active_page')->withQueryString();
        $completedJobOrders = $completedBase->latest()->paginate(5, ['*'], 'completed_page')->withQueryString();

        $activeAll = $allConcerns->whereIn('status', ['Open', 'In Progress']);
        $completedAll = $allConcerns->whereIn('status', ['Fixed', 'Closed']);

        $statusSummary = collect($issueColumns)->map(function ($types) use ($activeAll) {
            return $activeAll->whereIn('issue_type', $types)->count();
        });

        $totalIssues = $statusSummary->sum();
        $completedCount = $completedAll->count();

        $partsSummary = $allConcerns
            ->flatMap(function ($concern) {
                return $concern->usedItems->map(function ($used) {
                    return [
                        'name' => $used->inventoryItem->item_name ?? 'Item',
                        'qty' => (int) $used->qty_used,
                        'unit' => $used->inventoryItem->unit ?? '',
                    ];
                });
            })
            ->groupBy('name')
            ->map(fn ($items, $name) => [
                'name' => $name,
                'qty' => $items->sum('qty'),
                'unit' => $items->first()['unit'] ?? '',
            ])
            ->values();

        $timeline = $allConcerns->sortByDesc('updated_at')->values();

        $issueOptions = ['Camera', 'Monitor', 'DVR', 'Wiring', 'Power', 'Other'];
        $statusOptions = ['Open', 'In Progress', 'Fixed', 'Closed'];

        return view('it_department.concern.bus-status-show', compact(
            'bus',
            'issueColumns',
            'statusSummary',
            'totalIssues',
            'completedCount',
            'partsSummary',
            'activeJobOrders',
            'completedJobOrders',
            'timeline',
            'issueOptions',
            'statusOptions',
            'issue',
            'status'
        ));
    }
}
