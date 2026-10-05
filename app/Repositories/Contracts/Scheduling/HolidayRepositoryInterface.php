<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Scheduling;

use App\Models\Holiday;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface HolidayRepositoryInterface
{
    /**
     * Holidays observed in the year (and month, when not 0), by observed date.
     * Search on name, type and proclamation; exact type filter.
     *
     * @return LengthAwarePaginator<int, Holiday>
     */
    public function paginate(int $year, int $month, string $type, string $search, int $perPage = 20): LengthAwarePaginator;

    /** @return Collection<int, Holiday> every holiday observed in the year, by date */
    public function forYear(int $year): Collection;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Holiday;

    /** @param array<string, mixed> $attributes */
    public function update(Holiday $holiday, array $attributes): void;

    public function delete(Holiday $holiday): void;
}
