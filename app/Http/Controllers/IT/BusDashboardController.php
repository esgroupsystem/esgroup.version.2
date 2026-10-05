<?php

declare(strict_types=1);

namespace App\Http\Controllers\IT;

use App\Enums\CctvConcernStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\IT\BusConcernResource;
use App\Http\Resources\IT\BusDashboardRowResource;
use App\Models\BusDetail;
use App\Models\CctvConcern;
use App\Services\IT\BusDashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * IT Support → Bus Dashboard: active CCTV concerns per bus.
 */
final class BusDashboardController extends Controller
{
    public function __construct(
        private readonly BusDashboardService $dashboard,
    ) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('q', ''));

        return Inertia::render('it/cctv/bus-status', [
            'buses' => $this->dashboard->paginate($search)
                ->through(fn (BusDetail $bus): array => BusDashboardRowResource::make($bus)->resolve($request)),
            'columns' => $this->dashboard->columns(),
            'filters' => ['q' => $search],
            'urls' => [
                'index' => route('concern.bus-status'),
                'concerns' => route('concern.cctv.index'),
            ],
        ]);
    }

    public function show(Request $request, string $bodyNumber): Response
    {
        $issue = (string) $request->input('issue', '');
        $status = (string) $request->input('status', '');
        $data = $this->dashboard->busDetail($bodyNumber, $issue, $status);
        $bus = $data['bus'];
        $concern = fn (CctvConcern $concern): array => BusConcernResource::make($concern)->resolve($request);

        return Inertia::render('it/cctv/bus-status-show', [
            'bus' => [
                'body_number' => $bus->body_number,
                'display_name' => $bus->displayName(),
                'plate_number' => $bus->plate_number,
                'garage' => $bus->garage,
            ],
            'statusSummary' => $data['summary'],
            'totalIssues' => $data['total'],
            'completedCount' => $data['completedCount'],
            'partsSummary' => $data['parts'],
            'activeJobOrders' => $data['active']->through($concern),
            'completedJobOrders' => $data['completed']->through($concern),
            'timeline' => $data['timeline']->map(fn (CctvConcern $item): array => [
                'id' => $item->id,
                'jo_no' => $item->jo_no,
                'issue_type' => $item->issue_type,
                'status' => $item->status,
                'updated_at' => $item->updated_at?->format('M d, Y h:i A'),
            ])->values(),
            'issueOptions' => CctvConcern::ISSUE_TYPES,
            'statusOptions' => CctvConcernStatus::values(),
            'filters' => ['issue' => $issue, 'status' => $status],
            'urls' => [
                'self' => route('concern.bus-status.show', $bus->body_number),
                'back' => route('concern.bus-status'),
            ],
        ]);
    }
}
