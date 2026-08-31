<?php

declare(strict_types=1);

namespace App\Services\ITDepartment;

use App\Enums\ItTicketStatus;
use App\Models\JobOrder;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ItJobOrderDirectoryService
{
    private const CATEGORIES = [
        'ACCIDENT',
        'COLLECTING FARE',
        'CUTTING FARE',
        'RE- ISSUEING TICKET',
        'TAMPERING TICKET',
        'UNREGISTERED TICKET',
        'DELAYING ISSUANCE OF TICKET',
        'ROLLING TICKETS',
        'REMOVING HEADSTAB OF TICKET',
        'USING STUB TICKET',
        'WRONG CLOSING / OPEN',
        'OTHERS',
    ];

    /** @return LengthAwarePaginator<int, JobOrder> */
    public function paginateTab(string $tab, string $search, string $pageName = 'page'): LengthAwarePaginator
    {
        /** @var Builder<JobOrder> $query */
        $query = $this->applyTab($this->baseQuery($search), $tab);

        return $query
            ->paginate(10, ['*'], $pageName)
            ->withQueryString();
    }

    /** @return array<string, mixed> */
    public function indexData(string $search): array
    {
        $categoryCounts = JobOrder::query()
            ->select('job_type')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('job_type')
            ->pluck('total', 'job_type');

        return [
            'pending' => $this->paginateTab('pending', $search, 'pending_page')->appends(['tab' => 'pending']),
            'progress' => $this->paginateTab('progress', $search, 'progress_page')->appends(['tab' => 'progress']),
            'completed' => $this->paginateTab('completed', $search, 'completed_page')->appends(['tab' => 'completed']),
            'stats' => [
                'new' => JobOrder::query()->whereDate('created_at', today())->count(),
                'pending' => JobOrder::query()->whereIn('job_status', [ItTicketStatus::Pending->value, ItTicketStatus::Approval->value])->count(),
                'progress' => JobOrder::query()->where('job_status', ItTicketStatus::InProgress->value)->count(),
                'completed' => JobOrder::query()->where('job_status', ItTicketStatus::Completed->value)->count(),
            ],
            'categories' => collect(self::CATEGORIES)
                ->map(fn (string $category): array => [
                    'name' => $category,
                    'total' => (int) ($categoryCounts[$category] ?? 0),
                ])
                ->values()
                ->all(),
            'agents' => User::query()
                ->whereIn('role', ['IT Head', 'IT Officer', 'IT Technician'])
                ->withCount('jobOrdersAssigned')
                ->orderBy('full_name')
                ->get(),
        ];
    }

    /** @return Builder<JobOrder> */
    private function baseQuery(string $search): Builder
    {
        return JobOrder::query()
            ->with('bus')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('job_creator', 'like', "%{$search}%")
                        ->orWhere('job_type', 'like', "%{$search}%")
                        ->orWhere('job_status', 'like', "%{$search}%")
                        ->orWhere('driver_name', 'like', "%{$search}%")
                        ->orWhere('conductor_name', 'like', "%{$search}%")
                        ->orWhereHas('bus', function (Builder $bus) use ($search): void {
                            $bus->where('body_number', 'like', "%{$search}%")
                                ->orWhere('plate_number', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('job_date_filled');
    }

    /** @param Builder<JobOrder> $query @return Builder<JobOrder> */
    private function applyTab(Builder $query, string $tab): Builder
    {
        return match ($tab) {
            'progress' => $query->where('job_status', ItTicketStatus::InProgress->value),
            'completed' => $query->where('job_status', ItTicketStatus::Completed->value),
            default => $query->whereIn('job_status', [ItTicketStatus::Pending->value, ItTicketStatus::Approval->value]),
        };
    }
}
