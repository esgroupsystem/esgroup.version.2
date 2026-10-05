<?php

declare(strict_types=1);

namespace App\Services\Scheduling;

use App\Models\Holiday;
use App\Repositories\Contracts\Scheduling\HolidayRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Scheduling & Rates → Holiday Calendar. Payroll multipliers come from the holiday type
 * unless the user overrides them.
 */
final class HolidayService
{
    public function __construct(
        private readonly HolidayRepositoryInterface $holidays,
    ) {}

    /** @return LengthAwarePaginator<int, Holiday> */
    public function paginate(int $year, int $month, string $type, string $search): LengthAwarePaginator
    {
        return $this->holidays->paginate($year, $month, $type, $search);
    }

    /**
     * The year's holidays grouped by observed date (Y-m-d), for the calendar.
     *
     * @return Collection<string, Collection<int, array{name: string, type: string, moved_from: ?string}>>
     */
    public function calendar(int $year): Collection
    {
        return $this->holidays->forYear($year)
            ->groupBy(fn (Holiday $holiday): string => $holiday->observed_date->format('Y-m-d'))
            ->map(fn (Collection $day): Collection => $day->map(fn (Holiday $holiday): array => [
                'name' => $holiday->name,
                'type' => $holiday->holiday_type,
                'moved_from' => $holiday->is_moved ? $holiday->actual_date->format('M d') : null,
            ])->values());
    }

    /** @param array<string, mixed> $data validated form */
    public function create(array $data): Holiday
    {
        return $this->holidays->create($this->attributes($data));
    }

    /** @param array<string, mixed> $data validated form */
    public function update(Holiday $holiday, array $data): void
    {
        $this->holidays->update($holiday, $this->attributes($data));
    }

    public function delete(Holiday $holiday): void
    {
        $this->holidays->delete($holiday);
    }

    /**
     * Standard multipliers for the type, or the user's own (rounded to 2 places) when
     * "override_multipliers" is on.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        $override = (bool) ($data['override_multipliers'] ?? false);
        unset($data['override_multipliers']);

        $data['is_moved'] = (bool) ($data['is_moved'] ?? false);
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        if (! $override) {
            return array_merge($data, Holiday::standardMultipliers((string) $data['holiday_type']));
        }

        $data['not_worked_multiplier'] = round((float) ($data['not_worked_multiplier'] ?? 0), 2);
        $data['worked_multiplier'] = round((float) ($data['worked_multiplier'] ?? 0), 2);

        return $data;
    }
}
