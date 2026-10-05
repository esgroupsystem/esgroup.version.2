<?php

declare(strict_types=1);

namespace App\Services\IT;

use App\Enums\CctvConcernStatus;
use App\Models\BusDetail;
use App\Models\CctvConcern;
use App\Repositories\Contracts\Fleet\BusDetailRepositoryInterface;
use App\Repositories\Contracts\IT\CctvConcernRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Bus Dashboard: active CCTV concerns per bus, by dashboard column.
 */
final class BusDashboardService
{
    public function __construct(
        private readonly BusDetailRepositoryInterface $buses,
        private readonly CctvConcernRepositoryInterface $concerns,
    ) {}

    /** @return list<string> */
    public function columns(): array
    {
        return array_keys(CctvConcern::DASHBOARD_COLUMNS);
    }

    /**
     * Buses with the most active concerns first. Each bus gets `summary` (column => count) and `total`.
     *
     * @return LengthAwarePaginator<int, BusDetail>
     */
    public function paginate(string $search): LengthAwarePaginator
    {
        $active = CctvConcernStatus::activeValues();
        $buses = $this->buses->paginateByActiveCctvConcerns($search, $active, $this->countedIssueTypes());

        $byBus = $this->concerns
            ->forBuses($buses->getCollection()->modelKeys(), $active)
            ->groupBy('bus_no');

        $buses->getCollection()->each(function (BusDetail $bus) use ($byBus): void {
            $summary = $this->summary($byBus->get($bus->id, collect()));
            $bus->setAttribute('summary', $summary);
            $bus->setAttribute('total', array_sum($summary));
        });

        return $buses;
    }

    /**
     * Everything the bus detail page shows.
     *
     * @return array{bus: BusDetail, summary: array<string, int>, total: int, completedCount: int, parts: Collection<int, array{name: string, qty: int, unit: string}>, active: LengthAwarePaginator<int, CctvConcern>, completed: LengthAwarePaginator<int, CctvConcern>, timeline: Collection<int, CctvConcern>}
     */
    public function busDetail(string $bodyNumber, string $issue, string $status): array
    {
        $bus = $this->buses->findByBodyNumberOrFail($bodyNumber);
        $active = CctvConcernStatus::activeValues();
        $completed = CctvConcernStatus::completedValues();

        $all = $this->concerns->forBus($bus->id, CctvConcernStatus::values());
        $summary = $this->summary($all->whereIn('status', $active));

        return [
            'bus' => $bus,
            'summary' => $summary,
            'total' => array_sum($summary),
            'completedCount' => $all->whereIn('status', $completed)->count(),
            'parts' => $this->partsUsed($all),
            'active' => $this->concerns->paginateForBus($bus->id, $active, $issue, $status, 'active_page'),
            'completed' => $this->concerns->paginateForBus($bus->id, $completed, $issue, $status, 'completed_page'),
            'timeline' => $all->sortByDesc('updated_at')->values(),
        ];
    }

    /**
     * @param  Collection<int, CctvConcern>  $concerns
     * @return array<string, int>
     */
    private function summary(Collection $concerns): array
    {
        return array_map(
            fn (array $types): int => $concerns->whereIn('issue_type', $types)->count(),
            CctvConcern::DASHBOARD_COLUMNS,
        );
    }

    /** @return list<string> */
    private function countedIssueTypes(): array
    {
        return array_merge(...array_values(CctvConcern::DASHBOARD_COLUMNS));
    }

    /**
     * Parts used across the concerns, summed per item.
     *
     * @param  Collection<int, CctvConcern>  $concerns
     * @return Collection<int, array{name: string, qty: int, unit: string}>
     */
    private function partsUsed(Collection $concerns): Collection
    {
        return $concerns
            ->flatMap(fn (CctvConcern $concern) => $concern->usedItems->map(fn ($used): array => [
                'name' => $used->inventoryItem->item_name ?? 'Item',
                'qty' => (int) $used->qty_used,
                'unit' => $used->inventoryItem->unit ?? '',
            ]))
            ->groupBy('name')
            ->map(fn (Collection $items, int|string $name): array => [
                'name' => (string) $name,
                'qty' => (int) $items->sum('qty'),
                'unit' => (string) ($items->first()['unit'] ?? ''),
            ])
            ->values();
    }
}
