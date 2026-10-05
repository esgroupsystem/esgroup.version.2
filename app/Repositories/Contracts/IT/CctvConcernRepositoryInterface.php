<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\IT;

use App\Models\CctvConcern;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CctvConcernRepositoryInterface
{
    /**
     * Newest first, with bus, assignee and used items.
     *
     * @return LengthAwarePaginator<int, CctvConcern>
     */
    public function paginate(string $search, string $status, int $perPage = 10): LengthAwarePaginator;

    /** @return Collection<int, CctvConcern> every match of the list filters, newest first */
    public function allMatching(string $search, string $status): Collection;

    /**
     * Concerns of the given buses (bus_no) in the given statuses.
     *
     * @param  list<int>  $busIds
     * @param  list<string>  $statuses
     * @return Collection<int, CctvConcern>
     */
    public function forBuses(array $busIds, array $statuses): Collection;

    /**
     * @param  list<string>  $statuses
     * @return Collection<int, CctvConcern> newest first
     */
    public function forBus(int $busId, array $statuses): Collection;

    /**
     * @param  list<string>  $statuses
     * @return LengthAwarePaginator<int, CctvConcern>
     */
    public function paginateForBus(int $busId, array $statuses, string $issue, string $status, string $pageName, int $perPage = 5): LengthAwarePaginator;

    public function findOrFail(int $id): CctvConcern;

    /** Locked row with its used items. */
    public function findForUpdate(int $id): CctvConcern;

    /** Highest job order number of the year (locked), e.g. "JO-2026-00012". */
    public function lastNumberOfYear(int $year): ?string;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): CctvConcern;

    /** @param array<string, mixed> $attributes */
    public function update(CctvConcern $concern, array $attributes): void;

    public function delete(CctvConcern $concern): void;

    /** @param list<array{it_inventory_item_id: int, qty_used: int, remarks: ?string}> $rows */
    public function addUsedItems(CctvConcern $concern, array $rows): void;

    public function deleteUsedItems(CctvConcern $concern): void;
}
