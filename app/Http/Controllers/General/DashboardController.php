<?php

declare(strict_types=1);

namespace App\Http\Controllers\General;

use App\Enums\ItTicketStatus;
use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use App\Models\User;
use App\Services\General\DashboardService;
use App\Services\IT\JobOrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** General → Dashboard (dashboard.index) and the hidden IT dashboard (dashboard.itindex). */
final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly JobOrderService $jobOrders,
    ) {}

    public function index(): Response
    {
        return Inertia::render('dashboard/index', $this->dashboardService->greeting());
    }

    public function itindex(Request $request): Response
    {
        return Inertia::render('dashboards/it/index', [
            'stats' => $this->jobOrders->stats(),
            'weekly' => $this->dashboardService->weeklyTickets(),
            'categories' => collect($this->jobOrders->categorySummary())
                ->map(fn (array $category): array => ['label' => $category['name'], 'value' => $category['total']])
                ->sortByDesc('value')
                ->values(),
            'agents' => $this->jobOrders->agents()->map(fn (User $agent): array => [
                'id' => $agent->id,
                'name' => $agent->full_name,
                'role' => $agent->role,
                'assigned' => (int) $agent->job_orders_assigned_count,
            ])->values(),
            'unresolved' => $this->jobOrders->unresolved(8)->map(fn (JobOrder $job): array => [
                'id' => $job->id,
                'bus' => $job->busLabel(),
                'issue' => strtoupper((string) ($job->job_type ?: 'General')),
                'requester' => $job->job_creator ?: 'System',
                'assignee' => $job->job_assign_person,
                'status' => in_array($job->job_status, [ItTicketStatus::Pending->value, ItTicketStatus::Approval->value], true) ? 'Pending' : (string) $job->job_status,
                'age' => ($job->job_date_filled ?? $job->created_at) ? Carbon::parse($job->job_date_filled ?? $job->created_at)->diffForHumans() : null,
                'url' => $job->job_status === ItTicketStatus::Approval->value ? route('tickets.joborder.index') : route('tickets.joborder.view', $job->id),
            ]),
            'urls' => [
                'tickets' => route('tickets.joborder.index'),
                'create' => $request->user()?->can('tickets.create') ? route('tickets.createjoborder.index') : null,
            ],
        ]);
    }
}
