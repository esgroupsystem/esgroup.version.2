<?php

declare(strict_types=1);

namespace App\Repositories\IT;

use App\Models\CctvConcern;
use App\Models\CctvConcernItem;
use App\Repositories\Contracts\IT\CctvConcernRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class CctvConcernRepository implements CctvConcernRepositoryInterface
{
    private const RELATIONS = [
        'bus:id,garage,name,body_number,plate_number',
        'assignee:id,full_name',
        'usedItems.inventoryItem:id,item_name,unit,brand,model',
    ];

    public function paginate(string $search, string $status, int $perPage = 10): LengthAwarePaginator
    {
        return $this->filtered($search, $status)->latest()->paginate($perPage)->withQueryString();
    }

    public function allMatching(string $search, string $status): Collection
    {
        return $this->filtered($search, $status)->latest()->get();
    }

    public function forBuses(array $busIds, array $statuses): Collection
    {
        return CctvConcern::query()
            ->whereIn('bus_no', $busIds)
            ->whereIn('status', $statuses)
            ->get(['id', 'bus_no', 'issue_type', 'status']);
    }

    public function forBus(int $busId, array $statuses): Collection
    {
        return $this->forBusQuery($busId, $statuses)->latest()->get();
    }

    public function paginateForBus(int $busId, array $statuses, string $issue, string $status, string $pageName, int $perPage = 5): LengthAwarePaginator
    {
        return $this->forBusQuery($busId, $statuses)
            ->when($issue !== '', fn (Builder $query) => $query->where('issue_type', $issue))
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage, ['*'], $pageName)
            ->withQueryString();
    }

    public function findOrFail(int $id): CctvConcern
    {
        return CctvConcern::query()->findOrFail($id);
    }

    public function findForUpdate(int $id): CctvConcern
    {
        return CctvConcern::query()->with('usedItems')->lockForUpdate()->findOrFail($id);
    }

    public function lastNumberOfYear(int $year): ?string
    {
        $jobOrderNumber = CctvConcern::query()
            ->where('jo_no', 'like', "JO-{$year}-%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('jo_no');

        return $jobOrderNumber === null ? null : (string) $jobOrderNumber;
    }

    public function create(array $attributes): CctvConcern
    {
        return CctvConcern::query()->create($attributes);
    }

    public function update(CctvConcern $concern, array $attributes): void
    {
        $concern->update($attributes);
    }

    public function delete(CctvConcern $concern): void
    {
        $concern->delete();
    }

    public function addUsedItems(CctvConcern $concern, array $rows): void
    {
        if ($rows !== []) {
            $concern->usedItems()->saveMany(array_map(fn (array $row): CctvConcernItem => new CctvConcernItem($row), $rows));
        }
    }

    public function deleteUsedItems(CctvConcern $concern): void
    {
        $concern->usedItems()->delete();
    }

    /** @return Builder<CctvConcern> */
    private function filtered(string $search, string $status): Builder
    {
        return CctvConcern::query()->with(self::RELATIONS)->search($search)->status($status);
    }

    /**
     * @param  list<string>  $statuses
     * @return Builder<CctvConcern>
     */
    private function forBusQuery(int $busId, array $statuses): Builder
    {
        return CctvConcern::query()
            ->with(self::RELATIONS)
            ->where('bus_no', $busId)
            ->whereIn('status', $statuses);
    }
}
