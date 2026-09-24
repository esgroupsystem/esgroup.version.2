<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ItTicketStatus;
use App\Models\JobOrder;
use App\Services\ITDepartment\ItJobOrderDirectoryService;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $hour = (int) now('Asia/Manila')->format('H');

        [$greeting, $message] = match (true) {
            $hour < 12 => ['Good Morning', 'A fresh start for a productive day.'],
            $hour < 18 => ['Good Afternoon', 'Keep going, you are doing great today.'],
            default => ['Good Evening', 'Thank you for your hard work today.'],
        };

        return Inertia::render('dashboard/index', [
            'greeting' => $greeting,
            'message' => $message,
            'today' => now('Asia/Manila')->format('l, F d, Y'),
        ]);
    }

    public function itindex(ItJobOrderDirectoryService $directory): Response
    {
        $open = [ItTicketStatus::Pending->value, ItTicketStatus::Approval->value, ItTicketStatus::InProgress->value];

        // Six weeks, oldest first; one grouped query instead of a count per week and status.
        $weeks = collect(range(5, 0))->map(fn (int $ago) => now()->subWeeks($ago)->startOfWeek());
        $rows = JobOrder::query()
            ->where('created_at', '>=', $weeks->first())
            ->get(['job_status', 'created_at']);

        $weekly = fn (?array $statuses) => $weeks->map(fn ($start) => $rows
            ->filter(fn (JobOrder $job) => $job->created_at?->between($start, $start->copy()->endOfWeek())
                && ($statuses === null || in_array($job->job_status, $statuses, true)))
            ->count())->values();

        $indexData = $directory->indexData('');

        return Inertia::render('dashboards/it/index', [
            'stats' => $directory->stats(),
            'weekly' => [
                'labels' => $weeks->map(fn ($start) => $start->format('M d'))->values(),
                'created' => $weekly(null),
                'pending' => $weekly([ItTicketStatus::Pending->value, ItTicketStatus::Approval->value]),
                'progress' => $weekly([ItTicketStatus::InProgress->value]),
                'completed' => $weekly([ItTicketStatus::Completed->value]),
            ],
            'categories' => collect($indexData['categories'])
                ->map(fn (array $category): array => ['label' => $category['name'], 'value' => $category['total']])
                ->sortByDesc('value')
                ->values(),
            'agents' => $indexData['agents']->map(fn ($agent): array => [
                'id' => $agent->id,
                'name' => $agent->full_name,
                'role' => $agent->role,
                'assigned' => (int) $agent->job_orders_assigned_count,
            ])->values(),
            'unresolved' => JobOrder::query()
                ->with('bus')
                ->whereIn('job_status', $open)
                ->orderByDesc('job_date_filled')
                ->orderByDesc('id')
                ->limit(8)
                ->get()
                ->map(fn (JobOrder $job): array => [
                    'id' => $job->id,
                    'bus' => trim(($job->bus?->name ?? 'ES Transport').(($job->bus?->body_number ?? $job->bus?->plate_number) ? ' - '.($job->bus?->body_number ?? $job->bus?->plate_number) : '')),
                    'issue' => strtoupper((string) ($job->job_type ?: 'General')),
                    'requester' => $job->job_creator ?: 'System',
                    'assignee' => $job->job_assign_person,
                    'status' => in_array($job->job_status, [ItTicketStatus::Pending->value, ItTicketStatus::Approval->value], true) ? 'Pending' : (string) $job->job_status,
                    'age' => ($job->job_date_filled ?? $job->created_at) ? Carbon::parse($job->job_date_filled ?? $job->created_at)->diffForHumans() : null,
                    'url' => $job->job_status === ItTicketStatus::Approval->value ? route('tickets.joborder.index') : route('tickets.joborder.view', $job->id),
                ]),
            'urls' => [
                'tickets' => route('tickets.joborder.index'),
                'create' => auth()->user()?->can('tickets.create') ? route('tickets.createjoborder.index') : null,
            ],
        ]);
    }
}
