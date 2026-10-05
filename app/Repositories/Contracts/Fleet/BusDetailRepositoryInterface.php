<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Fleet;

use App\Models\BusDetail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BusDetailRepositoryInterface
{
    /** @return Collection<int, BusDetail> id, garage, name, body and plate number, by body then plate number */
    public function options(): Collection;

    /** @return Collection<int, BusDetail> every vehicle, by plate number */
    public function allByPlate(): Collection;

    public function lockForUpdate(int $id): ?BusDetail;

    /**
     * Search on garage, name, body and plate number.
     *
     * @param  'latest'|'plate'  $order  newest first (Bus List) or by plate number (Vehicle History)
     * @return LengthAwarePaginator<int, BusDetail>
     */
    public function paginate(string $search, string $order, int $perPage = 10): LengthAwarePaginator;

    /** @return Collection<int, string> garages in use, sorted */
    public function garages(): Collection;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): BusDetail;

    /** @param array<string, mixed> $attributes */
    public function update(BusDetail $bus, array $attributes): void;

    public function delete(BusDetail $bus): void;

    public function findByBodyNumberOrFail(string $bodyNumber): BusDetail;

    /**
     * Buses with the most active CCTV concerns first (across all pages), then by body number.
     * Each bus gets an `active_concerns_count` attribute.
     *
     * @param  list<string>  $statuses  concern statuses that count as active
     * @param  list<string>  $issueTypes  concern issue types that are counted
     * @return LengthAwarePaginator<int, BusDetail>
     */
    public function paginateByActiveCctvConcerns(string $search, array $statuses, array $issueTypes, int $perPage = 20): LengthAwarePaginator;
}
