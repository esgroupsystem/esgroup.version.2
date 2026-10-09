<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WorkdayType;
use App\Support\PayrollEmployeeNameFormatter;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $employee_biometric_id
 * @property string|null $crosschex_id
 * @property string|null $biometric_employee_id
 * @property string|null $employee_no
 * @property string|null $employee_name
 * @property \Carbon\CarbonInterface|null $work_date
 * @property string|null $shift_name
 * @property string|null $flexible_mode
 * @property array<int, mixed>|null $flexible_shift_options
 * @property WorkdayType|null $workday_type
 * @property int|null $paid_work_minutes
 * @property int|null $lunch_break_minutes
 * @property string|null $time_in
 * @property string|null $time_out
 * @property int|null $grace_minutes
 * @property string|null $status
 * @property string|null $day_off
 * @property array<string, mixed>|null $day_offs
 * @property string|null $remarks
 * @property-read mixed $formatted_time_in
 * @property-read mixed $formatted_time_out
 * @property-read mixed $is_flexible
 * @property-read mixed $is_permanent
 * @property-read mixed $payroll_display_name
 */
class EmployeePlottingSchedule extends Model
{
    public const STATUSES = ['scheduled', 'rest_day', 'inactive'];

    public const DEFAULT_STATUS = 'scheduled';

    public const SHIFTS = ['Regular Shift', 'Flexible Shift'];

    public const REGULAR_SHIFT = 'Regular Shift';

    public const FLEXIBLE_SHIFT = 'Flexible Shift';

    /**
     * Flexible Shift sub-modes:
     * - anytime: no fixed time at all, must complete the required clock hours any time in the day (legacy default).
     * - custom: one or more exact shift-time options (see `flexible_shift_options`); the actual time in is
     *   matched to the closest option, then late/undertime are computed the same way as Regular Shift.
     */
    public const FLEXIBLE_MODE_ANYTIME = 'anytime';

    public const FLEXIBLE_MODE_CUSTOM = 'custom';

    public const FLEXIBLE_MODES = [self::FLEXIBLE_MODE_ANYTIME, self::FLEXIBLE_MODE_CUSTOM];

    public const MAX_FLEXIBLE_SHIFT_OPTIONS = 10;

    public const WEEKDAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public const DEFAULT_GRACE_MINUTES = 15;

    protected $fillable = [
        'employee_biometric_id',
        'crosschex_id',
        'biometric_employee_id',
        'employee_no',
        'employee_name',
        'work_date',
        'shift_name',
        'flexible_mode',
        'flexible_shift_options',
        'workday_type',
        'paid_work_minutes',
        'lunch_break_minutes',
        'time_in',
        'time_out',
        'weekly_times',
        'grace_minutes',
        'status',
        'day_off',
        'day_offs',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'employee_biometric_id' => 'integer',
            'work_date' => 'date',
            'workday_type' => WorkdayType::class,
            'paid_work_minutes' => 'integer',
            'lunch_break_minutes' => 'integer',
            'grace_minutes' => 'integer',
            'day_offs' => 'array',
            'weekly_times' => 'array',
            'flexible_shift_options' => 'array',
        ];
    }

    /** @return BelongsTo<EmployeeBiometric, $this> */
    public function employeeBiometric(): BelongsTo
    {
        return $this->belongsTo(EmployeeBiometric::class, 'employee_biometric_id');
    }

    /**
     * @param  Builder<EmployeePlottingSchedule>  $query
     * @return Builder<EmployeePlottingSchedule>
     */
    public function scopePermanent(Builder $query): Builder
    {
        return $query->whereNull('work_date');
    }

    /** @return Builder<EmployeePlottingSchedule> */
    public function scopeForPayrollActiveEmployees(Builder $query): Builder
    {
        return $query->whereHas('employeeBiometric', function (Builder $query): void {
            $query->where('is_payroll_active', true)
                ->where('employment_status', EmployeeBiometric::STATUS_ACTIVE);
        });
    }

    public function getFormattedTimeInAttribute(): string
    {
        return $this->time_in ? Carbon::parse($this->time_in)->format('H:i') : '';
    }

    public function getFormattedTimeOutAttribute(): string
    {
        return $this->time_out ? Carbon::parse($this->time_out)->format('H:i') : '';
    }

    public function getIsFlexibleAttribute(): bool
    {
        return str_contains(strtolower((string) $this->shift_name), 'flexible');
    }

    /** Null when not a Flexible Shift; otherwise a valid mode, defaulting legacy rows to "anytime". */
    public function resolvedFlexibleMode(): ?string
    {
        if (! $this->is_flexible) {
            return null;
        }

        $mode = strtolower(trim((string) $this->flexible_mode));

        return in_array($mode, self::FLEXIBLE_MODES, true) ? $mode : self::FLEXIBLE_MODE_ANYTIME;
    }

    /**
     * Flexible Shift (Custom) shift-time options, only valid entries. Falls back to the single
     * time_in/time_out pair (legacy rows saved before multiple options existed).
     *
     * @return list<array{time_in: string, time_out: string}>
     */
    public function resolvedShiftOptions(): array
    {
        $raw = $this->getAttribute('flexible_shift_options');
        $options = [];

        foreach (is_array($raw) ? $raw : [] as $entry) {
            $in = self::cleanTime(data_get($entry, 'time_in'));
            $out = self::cleanTime(data_get($entry, 'time_out'));

            if ($in !== null && $out !== null) {
                $options[] = ['time_in' => $in, 'time_out' => $out];
            }
        }

        if ($options !== []) {
            return $options;
        }

        $in = self::cleanTime($this->time_in);
        $out = self::cleanTime($this->time_out);

        return $in !== null && $out !== null ? [['time_in' => $in, 'time_out' => $out]] : [];
    }

    /**
     * The shift-time option whose time in is closest to the employee's actual time in
     * ("detect" which of the employee's two-or-more allowed schedules they clocked into).
     * Falls back to the first option when there is no actual time in to match against.
     *
     * @return array{time_in: string, time_out: string}|null
     */
    public function matchShiftOption(Carbon $workDate, ?CarbonInterface $actualTimeIn): ?array
    {
        $options = $this->resolvedShiftOptions();

        if ($options === []) {
            return null;
        }

        if ($actualTimeIn === null) {
            return $options[0];
        }

        $best = null;
        $bestDiff = null;

        foreach ($options as $option) {
            $scheduledIn = Carbon::parse($workDate->toDateString().' '.$option['time_in'], 'Asia/Manila');
            $diff = abs($scheduledIn->diffInMinutes($actualTimeIn));

            if ($bestDiff === null || $diff < $bestDiff) {
                $best = $option;
                $bestDiff = $diff;
            }
        }

        return $best;
    }

    public function getIsPermanentAttribute(): bool
    {
        return is_null($this->work_date);
    }

    public function resolvedWorkdayType(): WorkdayType
    {
        return $this->workday_type
            ?? WorkdayType::fromPaidMinutes((int) ($this->paid_work_minutes ?: 480));
    }

    public function paidWorkMinutes(): int
    {
        $minutes = (int) ($this->paid_work_minutes ?? 0);

        return in_array($minutes, [480, 540], true)
            ? $minutes
            : $this->resolvedWorkdayType()->paidMinutes();
    }

    public function lunchBreakMinutes(): int
    {
        return max(0, (int) ($this->lunch_break_minutes ?? 60));
    }

    public function requiredClockMinutes(): int
    {
        return $this->paidWorkMinutes() + $this->lunchBreakMinutes();
    }

    public function paidWorkHours(): float
    {
        return round($this->paidWorkMinutes() / 60, 2);
    }

    /**
     * Per-weekday times of a "different time per day" schedule, only valid entries.
     * Empty = the same time every working day.
     *
     * @return array<string, array{time_in: string, time_out: string, workday_type: string}>
     */
    public function weeklyTimes(): array
    {
        $raw = $this->getAttribute('weekly_times');
        $times = [];

        foreach (is_array($raw) ? $raw : [] as $day => $entry) {
            $day = ucfirst(strtolower(trim((string) $day)));
            $type = WorkdayType::tryFrom((string) data_get($entry, 'workday_type', ''));
            $in = self::cleanTime(data_get($entry, 'time_in'));
            $out = self::cleanTime(data_get($entry, 'time_out'));

            if (in_array($day, self::WEEKDAYS, true) && $type !== null && $in !== null && $out !== null) {
                $times[$day] = ['time_in' => $in, 'time_out' => $out, 'workday_type' => $type->value];
            }
        }

        return $times;
    }

    public function hasWeeklyTimes(): bool
    {
        return $this->weeklyTimes() !== [];
    }

    /**
     * This schedule as it applies on one date: a "different time per day" permanent schedule
     * swaps in that weekday's time in / out and work hours. The copy is for reading only and
     * must never be saved. Dated schedules and fixed schedules come back unchanged.
     */
    public function forDate(CarbonInterface|string $date): static
    {
        $day = ($date instanceof CarbonInterface ? $date : Carbon::parse($date))->format('l');
        $times = $this->work_date === null ? ($this->weeklyTimes()[$day] ?? null) : null;

        if ($times === null) {
            return $this;
        }

        $type = WorkdayType::from($times['workday_type']);
        $copy = clone $this;
        $copy->setRawAttributes(array_merge($this->getAttributes(), [
            'time_in' => $times['time_in'].':00',
            'time_out' => $times['time_out'].':00',
            'workday_type' => $type->value,
            'paid_work_minutes' => $type->paidMinutes(),
            'lunch_break_minutes' => $type->lunchMinutes(),
        ]), true);

        return $copy;
    }

    private static function cleanTime(mixed $value): ?string
    {
        $value = trim((string) $value);

        return preg_match('/^([01]\d|2[0-3]):[0-5]\d/', $value) ? substr($value, 0, 5) : null;
    }

    public function resolvedDayOffs(): array
    {
        /** @var array<int, mixed>|null $dayOffs */
        $dayOffs = $this->getAttribute('day_offs');

        if (is_array($dayOffs) && $dayOffs !== []) {
            return $this->normalizeDayOffs($dayOffs);
        }

        return $this->normalizeDayOffs(
            preg_split('/\s*,\s*/', (string) ($this->day_off ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: []
        );
    }

    public function isDayOffOn(Carbon $date): bool
    {
        return in_array($date->format('l'), $this->resolvedDayOffs(), true);
    }

    private function normalizeDayOffs(array $dayOffs): array
    {
        $validDays = self::WEEKDAYS;

        return collect($dayOffs)
            ->map(fn (mixed $day): string => ucfirst(strtolower(trim((string) $day))))
            ->filter(fn (string $day): bool => in_array($day, $validDays, true))
            ->unique()
            ->sortBy(fn (string $day): int => array_search($day, $validDays, true))
            ->values()
            ->all();
    }

    public function getPayrollDisplayNameAttribute(): string
    {
        return PayrollEmployeeNameFormatter::display($this->employee_name ?? null);
    }
}
