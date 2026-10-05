<?php

declare(strict_types=1);

namespace App\Services\Maintenance;

use App\Models\BusDetail;
use App\Models\Location;
use App\Repositories\Contracts\Fleet\BusDetailRepositoryInterface;
use App\Repositories\Contracts\Maintenance\LocationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Shared look-ups of the inventory pages (Parts Issuance, Receiving Area, Stock Transfer):
 * the stockrooms a user may use and the vehicle list.
 */
final class InventoryDirectoryService
{
    public function __construct(
        private readonly LocationRepositoryInterface $locations,
        private readonly BusDetailRepositoryInterface $vehicles,
    ) {}

    /** The stockroom the user is tied to (`users.location_id`), or null when they see every stockroom. */
    public function userLocationId(Request $request): ?int
    {
        $locationId = $request->user()?->location_id;

        return $locationId ? (int) $locationId : null;
    }

    /** 403 when the user is tied to another stockroom than the record's. */
    public function assertLocationAccess(Request $request, int $locationId, string $message): void
    {
        $userLocationId = $this->userLocationId($request);
        abort_if($userLocationId !== null && $userLocationId !== $locationId, 403, $message);
    }

    /**
     * Product ids already picked in a form (`exclude_ids=1,2,3` or `exclude_ids[]=1`), left out
     * of the product search.
     *
     * @return list<int>
     */
    public function excludeIds(Request $request): array
    {
        $raw = $request->input('exclude_ids', '');

        return collect(is_array($raw) ? Arr::flatten($raw) : explode(',', (string) $raw))
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /** @return Collection<int, Location> active stockrooms by name, only $locationId when given */
    public function activeLocations(?int $locationId = null): Collection
    {
        return $this->locations->active($locationId);
    }

    /** @return Collection<int, array{value: string, label: string}> */
    public function locationOptions(?int $locationId = null): Collection
    {
        return $this->activeLocations($locationId)
            ->map(fn (Location $location): array => ['value' => (string) $location->id, 'label' => (string) $location->name])
            ->values();
    }

    /** @return Collection<int, array{value: string, label: string, hint: string}> */
    public function vehicleOptions(): Collection
    {
        return $this->vehicles->allByPlate()
            ->map(fn (BusDetail $vehicle): array => [
                'value' => (string) $vehicle->id,
                'label' => ($vehicle->plate_number ?? 'N/A').' — '.($vehicle->body_number ?? 'No Body No.'),
                'hint' => trim(($vehicle->name ?? '').($vehicle->garage ? ' · '.$vehicle->garage : '')),
            ])
            ->values();
    }
}
