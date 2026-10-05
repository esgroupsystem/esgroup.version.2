<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Models\BusDetail;
use App\Repositories\Contracts\Fleet\BusDetailRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Bus List (`bus_details`): the vehicles parts are issued to. */
final class BusListService
{
    public function __construct(private readonly BusDetailRepositoryInterface $vehicles) {}

    /** @return LengthAwarePaginator<int, BusDetail> newest first */
    public function paginate(string $search): LengthAwarePaginator
    {
        return $this->vehicles->paginate(trim($search), 'latest');
    }

    /** @return Collection<int, string> garages already in use, for the form suggestions */
    public function garages(): Collection
    {
        return $this->vehicles->garages();
    }

    /** @param array{garage: string, name: string, body_number: string, plate_number: string} $data */
    public function create(array $data): BusDetail
    {
        return $this->vehicles->create($data);
    }

    /** @param array{garage: string, name: string, body_number: string, plate_number: string} $data */
    public function update(BusDetail $bus, array $data): void
    {
        $this->vehicles->update($bus, $data);
    }

    public function delete(BusDetail $bus): void
    {
        $this->vehicles->delete($bus);
    }
}
