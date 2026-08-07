<?php

namespace App\Models;

use App\Enums\WorkdayType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePlottingSchedule extends Model
{
    protected $fillable = [
        'employee_biometric_id',
        'crosschex_id',
        'biometric_employee_id',
        'employee_no',
        'employee_name',
        'work_date',
        'shift_name',
        'workday_type',
        'paid_work_minutes',
        'lunch_break_minutes',
        'time_in',
        'time_out',
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
        ];
    }

    public function employeeBiometric(): BelongsTo
    {
        return $this->belongsTo(EmployeeBiometric::class, 'employee_biometric_id');
    }

    public function scopePermanent(Builder $query): Builder
    {
        return $query->whereNull('work_date');
    }

    public function scopeForPayrollActiveEmployees(Builder $query): Builder
    {
        return $query->whereHas('employeeBiometric', function (Builder $query): void {
            $query->payrollActive();
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

    public function getIsPermanentAttribute(): bool
    {
        return is_null($this->work_date);
    }

    public function resolvedWorkdayType(): WorkdayType
    {
        if ($this->workday_type instanceof WorkdayType) {
            return $this->workday_type;
        }

        if (is_string($this->workday_type)) {
            $resolved = WorkdayType::tryFrom($this->workday_type);

            if ($resolved) {
                return $resolved;
            }
        }

        return WorkdayType::fromPaidMinutes((int) ($this->paid_work_minutes ?: 480));
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

    public function resolvedDayOffs(): array
    {
        $dayOffs = $this->day_offs;

        if (is_array($dayOffs) && $dayOffs !== []) {
            return $this->normalizeDayOffs($dayOffs);
        }

        if (is_string($dayOffs) && trim($dayOffs) !== '') {
            $decoded = json_decode($dayOffs, true);

            if (is_array($decoded)) {
                return $this->normalizeDayOffs($decoded);
            }
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
        $validDays = [
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday',
        ];

        return collect($dayOffs)
            ->map(fn (mixed $day): string => ucfirst(strtolower(trim((string) $day))))
            ->filter(fn (string $day): bool => in_array($day, $validDays, true))
            ->unique()
            ->sortBy(fn (string $day): int => array_search($day, $validDays, true))
            ->values()
            ->all();
    }
}
