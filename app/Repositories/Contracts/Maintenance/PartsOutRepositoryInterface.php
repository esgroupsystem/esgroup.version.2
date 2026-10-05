<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Maintenance;

use App\Models\BusDetail;
use App\Models\PartsOut;
use App\Models\PartsOutItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Parts Issuance (parts_outs): stock issued to a vehicle from one location. */
interface PartsOutRepositoryInterface
{
    /**
     * Newest first; search on number, mechanic, requester, job order, date, location and vehicle.
     *
     * @return LengthAwarePaginator<int, PartsOut>
     */
    public function paginate(string $search, ?int $locationId, int $perPage = 10): LengthAwarePaginator;

    /** Vehicle, creator, location and items with their product. */
    public function loadForShow(PartsOut $partsOut): PartsOut;

    /** Locked record with items and products. */
    public function findForUpdate(int $id): PartsOut;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): PartsOut;

    /** @param array<string, mixed> $attributes */
    public function update(PartsOut $partsOut, array $attributes): void;

    /** Soft-deletes the record. */
    public function delete(PartsOut $partsOut): void;

    /** @param array<string, mixed> $attributes */
    public function addItem(PartsOut $partsOut, array $attributes): PartsOutItem;

    /**
     * Posted issuances of one vehicle (Vehicle History), searchable by record and part fields.
     *
     * @return LengthAwarePaginator<int, PartsOut>
     */
    public function paginatePostedForVehicle(BusDetail $vehicle, string $search, int $perPage = 10): LengthAwarePaginator;

    /** @return array{transactions: int, parts_used: int, latest: ?string, most_used: ?PartsOutItem} totals of a vehicle's posted issuances */
    public function vehicleTotals(BusDetail $vehicle): array;
}
