<?php

declare(strict_types=1);

namespace App\Repositories\Scheduling;

use App\Models\Holiday;
use App\Repositories\Contracts\Scheduling\HolidayRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class HolidayRepository implements HolidayRepositoryInterface
{
    public function paginate(int $year, int $month, string $type, string $search, int $perPage = 20): LengthAwarePaginator
    {
        return Holiday::query()
            ->whereYear('observed_date', $year)
            ->when($month !== 0, fn (Builder $query) => $query->whereMonth('observed_date', $month))
            ->when($type !== '', fn (Builder $query) => $query->where('holiday_type', $type))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('holiday_type', 'like', "%{$search}%")
                ->orWhere('source_proclamation', 'like', "%{$search}%")))
            ->orderBy('observed_date')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function forYear(int $year): Collection
    {
        return Holiday::query()->whereYear('observed_date', $year)->orderBy('observed_date')->get();
    }

    public function create(array $attributes): Holiday
    {
        return Holiday::query()->create($attributes);
    }

    public function update(Holiday $holiday, array $attributes): void
    {
        $holiday->update($attributes);
    }

    public function delete(Holiday $holiday): void
    {
        $holiday->delete();
    }
}
