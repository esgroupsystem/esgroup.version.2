<?php

declare(strict_types=1);

namespace App\Repositories\Maintenance;

use App\Models\Location;
use App\Repositories\Contracts\Maintenance\LocationRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class LocationRepository implements LocationRepositoryInterface
{
    public function active(?int $onlyId = null): Collection
    {
        return Location::query()
            ->where('is_active', true)
            ->when($onlyId !== null, fn (Builder $query) => $query->whereKey($onlyId))
            ->orderBy('name')
            ->get();
    }

    public function all(): Collection
    {
        return Location::query()->orderBy('name')->get();
    }

    public function isActive(int $id): bool
    {
        return Location::query()->whereKey($id)->where('is_active', true)->exists();
    }

    public function findForUpdate(int $id): Location
    {
        return Location::query()->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function findActiveForUpdate(int $id): ?Location
    {
        return Location::query()->whereKey($id)->where('is_active', true)->lockForUpdate()->first();
    }
}
