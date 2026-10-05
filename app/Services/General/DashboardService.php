<?php

declare(strict_types=1);

namespace App\Services\General;

use App\Enums\ItTicketStatus;
use App\Models\JobOrder;
use App\Services\IT\JobOrderService;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/** The home dashboard greeting and the hidden IT dashboard (ticket stats over six weeks). */
final class DashboardService
{
    public function __construct(private readonly JobOrderService $jobOrders) {}

    /** @return array{greeting: string, message: string, today: string} Manila time */
    public function greeting(): array
    {
        $now = now('Asia/Manila');

        [$greeting, $message] = match (true) {
            (int) $now->format('H') < 12 => ['Good Morning', 'A fresh start for a productive day.'],
            (int) $now->format('H') < 18 => ['Good Afternoon', 'Keep going, you are doing great today.'],
            default => ['Good Evening', 'Thank you for your hard work today.'],
        };

        return ['greeting' => $greeting, 'message' => $message, 'today' => $now->format('l, F d, Y')];
    }

    /**
     * Tickets created per week for the last six weeks (oldest first), in total and per status group.
     * One query for all weeks.
     *
     * @return array{labels: Collection<int, string>, created: Collection<int, int>, pending: Collection<int, int>, progress: Collection<int, int>, completed: Collection<int, int>}
     */
    public function weeklyTickets(): array
    {
        $weeks = collect(range(5, 0))->map(fn (int $ago): CarbonInterface => now()->subWeeks($ago)->startOfWeek());
        $rows = $this->jobOrders->createdSince($weeks->first());

        $weekly = fn (?array $statuses): Collection => $weeks->map(fn (CarbonInterface $start): int => $rows
            ->filter(fn (JobOrder $job): bool => (bool) $job->created_at?->between($start, $start->copy()->endOfWeek())
                && ($statuses === null || in_array($job->job_status, $statuses, true)))
            ->count())->values();

        return [
            'labels' => $weeks->map(fn (CarbonInterface $start): string => $start->format('M d'))->values(),
            'created' => $weekly(null),
            'pending' => $weekly([ItTicketStatus::Pending->value, ItTicketStatus::Approval->value]),
            'progress' => $weekly([ItTicketStatus::InProgress->value]),
            'completed' => $weekly([ItTicketStatus::Completed->value]),
        ];
    }
}
