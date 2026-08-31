<?php

declare(strict_types=1);

namespace App\Http\Controllers\IT_Department;

use App\Enums\CctvConcernStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ITDepartment\StoreCctvConcernRequest;
use App\Http\Requests\ITDepartment\UpdateCctvConcernRequest;
use App\Models\BusDetail;
use App\Models\CctvConcern;
use App\Models\User;
use App\Services\ITDepartment\CctvConcernDirectoryService;
use App\Services\ITDepartment\CctvConcernService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class CctvController extends Controller
{
    public function __construct(
        private readonly CctvConcernService $concernService,
        private readonly CctvConcernDirectoryService $directoryService,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));

        if ($status !== '' && ! in_array($status, CctvConcernStatus::values(), true)) {
            $status = '';
        }

        return view('it_department.concern.index', $this->directoryService->indexData($search, $status));
    }

    public function store(StoreCctvConcernRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);

        try {
            $this->concernService->create($request->validated(), $user);

            return redirect()
                ->route('concern.cctv.index')
                ->with('success', 'CCTV Job Order created successfully.');
        } catch (Throwable $exception) {
            return redirect()
                ->route('concern.cctv.index')
                ->withInput()
                ->withErrors(['error' => $exception->getMessage()]);
        }
    }

    public function update(UpdateCctvConcernRequest $request, int $id): RedirectResponse
    {
        try {
            $jobOrder = CctvConcern::query()->findOrFail($id);
            $this->concernService->update($jobOrder, $request->validated());

            return back()->with('success', 'Job Order updated.');
        } catch (Throwable $exception) {
            return back()
                ->withInput()
                ->withErrors(['error' => $exception->getMessage()]);
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->concernService->delete(CctvConcern::query()->findOrFail($id));

            return redirect()
                ->route('concern.cctv.index')
                ->with('success', 'Job Order deleted.');
        } catch (Throwable $exception) {
            return redirect()
                ->route('concern.cctv.index')
                ->withErrors(['error' => $exception->getMessage()]);
        }
    }

    public function view(int $id)
    {
        $jobOrder = CctvConcern::with([
            'assignee',
            'creator',
            'usedItems.inventoryItem',
        ])->findOrFail($id);

        return view('it_department.concern.index', compact('jobOrder'));
    }

    public function acceptTask(int $id)
    {
        return back();
    }

    public function markAsDone(int $id)
    {
        return back();
    }

    public function addNote(Request $request, int $id)
    {
        return back();
    }

    public function addFiles(Request $request, int $id)
    {
        return back();
    }

    public function export(Request $request, string $type)
    {
        $q = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));

        $busDisplayMap = BusDetail::query()
            ->get(['id', 'body_number', 'plate_number', 'name'])
            ->mapWithKeys(function ($bus) {
                return [
                    $bus->id => implode(' - ', array_filter([
                        $bus->body_number,
                        $bus->plate_number,
                        $bus->name,
                    ])),
                ];
            });

        $jobOrders = CctvConcern::query()
            ->with([
                'bus:id,garage,name,body_number,plate_number',
                'assignee:id,full_name',
                'usedItems.inventoryItem:id,item_name,unit,brand,model',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($x) use ($q) {
                    $x->where('jo_no', 'like', "%{$q}%")
                        ->orWhereHas('bus', function ($bus) use ($q) {
                            $bus->where('body_number', 'like', "%{$q}%")
                                ->orWhere('plate_number', 'like', "%{$q}%")
                                ->orWhere('name', 'like', "%{$q}%")
                                ->orWhere('garage', 'like', "%{$q}%");
                        })
                        ->orWhere('reported_by', 'like', "%{$q}%")
                        ->orWhere('issue_type', 'like', "%{$q}%")
                        ->orWhere('problem_details', 'like', "%{$q}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest('created_at')
            ->get();

        if ($type === 'print') {
            return view('it_department.concern.print', [
                'jobOrders' => $jobOrders,
                'busDisplayMap' => $busDisplayMap,
                'status' => $status ?: 'All',
                'q' => $q,
            ]);
        }

        if ($type !== 'csv') {
            abort(404);
        }

        $fileName = 'cctv-job-orders-'.strtolower(str_replace(' ', '-', $status ?: 'all')).'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($jobOrders, $busDisplayMap) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'JO No',
                'Bus Details',
                'Reporter',
                'Issue Type',
                'Problem Details',
                'Action Taken',
                'Status',
                'Assignee',
                'Items Used',
                'Created Date',
                'Fixed Date',
            ], ',', '"', '\\');

            foreach ($jobOrders as $jo) {
                $itemsUsed = $jo->usedItems->map(function ($used) {
                    $itemName = $used->inventoryItem->item_name ?? 'Item';
                    $unit = $used->inventoryItem->unit ?? '';

                    return trim($itemName.' x'.$used->qty_used.' '.$unit);
                })->implode(', ');

                fputcsv($handle, [
                    $jo->jo_no,
                    $busDisplayMap[$jo->bus_no] ?? $jo->bus_no,
                    $jo->reported_by,
                    $jo->issue_type,
                    $jo->problem_details,
                    $jo->action_taken,
                    $jo->status,
                    optional($jo->assignee)->full_name,
                    $itemsUsed,
                    optional($jo->created_at)->format('Y-m-d h:i A'),
                    optional($jo->fixed_at)->format('Y-m-d h:i A'),
                ], ',', '"', '\\');
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function busStatus(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

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
            ->with([
                'bus:id,garage,name,body_number,plate_number',
                'assignee:id,full_name',
                'usedItems.inventoryItem:id,item_name,unit,brand',
            ])
            ->whereIn('status', ['Open', 'In Progress', 'Fixed', 'Closed'])
            ->get();

        $activeConcerns = $allConcerns->whereIn('status', ['Open', 'In Progress']);
        $concernsByBus = $activeConcerns->groupBy('bus_no');
        $allConcernsByBus = $allConcerns->groupBy('bus_no');

        $collection = $buses->getCollection()
            ->map(function (BusDetail $bus) use ($concernsByBus, $allConcernsByBus, $issueColumns): BusDetail {
                $busConcerns = $concernsByBus->get($bus->id, collect());
                $allBusConcerns = $allConcernsByBus->get($bus->id, collect());

                $bus->setAttribute('display_name', implode(' - ', array_filter([
                    $bus->body_number,
                    $bus->plate_number,
                    $bus->name,
                    $bus->garage,
                ])));

                $bus->setAttribute('status_summary', collect($issueColumns)->map(function ($types) use ($busConcerns) {
                    return $busConcerns->whereIn('issue_type', $types)->count();
                }));

                $bus->setAttribute('total_issues', $bus->getAttribute('status_summary')->sum());
                $bus->setAttribute('completed_count', $allBusConcerns->whereIn('status', ['Fixed', 'Closed'])->count());

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
        $issue = $request->input('issue');
        $status = $request->input('status');

        $issueColumns = [
            'CCTV' => ['Camera', 'Wiring'],
            'DVR' => ['DVR'],
            'Monitor' => ['Monitor'],
            'Power Supply' => ['Power Supply', 'Power'],
            'Other' => ['Other'],
        ];

        $bus = BusDetail::query()
            ->where('body_number', '=', $bodyNumber, 'and')
            ->firstOrFail();

        $bus->display_name = implode(' - ', array_filter([
            $bus->body_number,
            $bus->plate_number,
            $bus->name,
            $bus->garage,
        ]));

        $allConcernsBase = CctvConcern::query()
            ->with([
                'bus:id,garage,name,body_number,plate_number',
                'assignee:id,full_name',
                'usedItems.inventoryItem:id,item_name,unit,brand',
            ])
            ->where('bus_no', $bus->id)
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
