<?php

declare(strict_types=1);

namespace App\Repositories\HR;

use App\Models\HrOffense;
use App\Repositories\Contracts\HR\HrOffenseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class HrOffenseRepository implements HrOffenseRepositoryInterface
{
    public function paginate(string $search, string $type, string $gravity, ?int $id, int $perPage = 10): LengthAwarePaginator
    {
        return HrOffense::query()
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('section', 'like', "%{$search}%")
                ->orWhere('offense_description', 'like', "%{$search}%")))
            ->when($type !== '', fn (Builder $query) => $query->where('offense_type', $type))
            ->when($gravity !== '', fn (Builder $query) => $query->where('offense_gravity', $gravity))
            ->when($id !== null, fn (Builder $query) => $query->whereKey($id))
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allBySection(): Collection
    {
        return HrOffense::query()->orderBy('section')->get();
    }

    public function create(array $attributes): HrOffense
    {
        return HrOffense::query()->create($attributes);
    }
}
