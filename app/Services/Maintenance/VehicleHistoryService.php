<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Models\BusDetail;
use App\Models\PartsOut;
use App\Repositories\Contracts\Fleet\BusDetailRepositoryInterface;
use App\Repositories\Contracts\Maintenance\PartsOutRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Vehicle History: the parts issued to each vehicle (posted Parts Issuance records only). */
final class VehicleHistoryService
{
    public function __construct(
        private readonly BusDetailRepositoryInterface $vehicles,
        private readonly PartsOutRepositoryInterface $partsOuts,
    ) {}

    /** @return LengthAwarePaginator<int, BusDetail> by plate number */
    public function buses(string $search): LengthAwarePaginator
    {
        return $this->vehicles->paginate(trim($search), 'plate');
    }

    /**
     * @return array{records: LengthAwarePaginator<int, PartsOut>, transactions: int, parts_used: int, latest: ?string, most_used: ?\App\Models\PartsOutItem}
     */
    public function history(BusDetail $bus, string $search): array
    {
        return [
            'records' => $this->partsOuts->paginatePostedForVehicle($bus, trim($search)),
            ...$this->partsOuts->vehicleTotals($bus),
        ];
    }
}
