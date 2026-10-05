<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Maintenance;

use App\Models\Location;
use Illuminate\Support\Collection;

/** Stock locations (Main, Balintawak, ...). */
interface LocationRepositoryInterface
{
    /** @return Collection<int, Location> active locations by name, or only $onlyId */
    public function active(?int $onlyId = null): Collection;

    /** @return Collection<int, Location> every location by name */
    public function all(): Collection;

    public function isActive(int $id): bool;

    /** Locked location; fails when missing. */
    public function findForUpdate(int $id): Location;

    /** Locked active location, or null. */
    public function findActiveForUpdate(int $id): ?Location;
}
