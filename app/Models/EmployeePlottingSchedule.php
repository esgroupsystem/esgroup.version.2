<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WorkdayType;
use App\Support\PayrollEmployeeNameFormatter;
use Carbon\Carbon;
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

    public function getPayrollDisplayNameAttribute(): string
    {
        return PayrollEmployeeNameFormatter::display($this->employee_name ?? null);
    }
}
