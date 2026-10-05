<?php

declare(strict_types=1);

namespace App\Repositories\Payroll;

use App\Models\DailyAttendanceSummary;
use App\Models\EmployeeBiometric;
use App\Models\PayrollItem;
use App\Repositories\Contracts\Payroll\AttendanceSummaryRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class AttendanceSummaryRepository implements AttendanceSummaryRepositoryInterface
{
    /** Statuses HR should look at. */
    public const NEEDS_REVIEW = ['half_day', 'incomplete_log', 'no_schedule', 'holiday_unpaid', 'absent'];

    private const REVIEW_SQL = "'half_day','incomplete_log','no_schedule','holiday_unpaid','absent'";

    /** Stat card => SQL condition counted per row. */
    private const COUNTS = [
        'present' => "attendance_status IN ('present','adjusted_present')",
        'payable_records' => 'payable_days > 0',
        'needs_review' => 'attendance_status IN ('.self::REVIEW_SQL.')',
        'half_day' => "attendance_status = 'half_day'",
        'late_undertime_records' => "attendance_status IN ('late','undertime','late_undertime')",
        'late' => "attendance_status IN ('late','late_undertime')",
        'undertime' => "attendance_status IN ('undertime','late_undertime','half_day')",
        'absent' => "attendance_status = 'absent'",
        'incomplete' => "attendance_status = 'incomplete_log'",
        'no_schedule' => "attendance_status = 'no_schedule'",
        'holiday' => 'is_holiday = 1',
        'holiday_paid' => 'is_holiday = 1 AND payable_days > 0',
        'holiday_unpaid' => "attendance_status = 'holiday_unpaid'",
        'holiday_worked' => "attendance_status = 'holiday_worked'",
        'regular_holiday_worked' => "attendance_status = 'holiday_worked' AND holiday_type LIKE '%regular%' AND holiday_type NOT LIKE '%non%' AND holiday_type NOT LIKE '%special%'",
        'special_holiday_worked' => "attendance_status = 'holiday_worked' AND (holiday_type LIKE '%special%' OR holiday_type LIKE '%non%')",
        'rest_day' => 'is_rest_day = 1',
        'rest_day_paid' => 'is_rest_day = 1 AND payable_days > 0',
        'leave' => 'is_leave = 1',
        'adjustment' => 'has_adjustment = 1',
        'regular_shift' => "shift_name LIKE '%Regular%'",
        'flexible_shift' => "shift_name LIKE '%Flexible%'",
    ];

    /** Stat card => summed column (minutes are whole numbers). */
    private const SUMS = [
        'total_late_minutes' => 'late_minutes',
        'total_undertime_minutes' => 'undertime_minutes',
        'total_worked_minutes' => 'worked_minutes',
        'total_overtime_minutes' => 'overtime_minutes',
        'total_payable_days' => 'payable_days',
        'total_payable_hours' => 'payable_hours',
    ];

    public function paginate(CarbonInterface $start, CarbonInterface $end, array $filters, int $perPage = 25): LengthAwarePaginator
    {
        return $this->filtered($start, $end, $filters)
            ->with(['employeeBiometric', 'plottingSchedule'])
            ->orderBy('work_date')
            ->orderBy('employee_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allMatching(CarbonInterface $start, CarbonInterface $end, array $filters): Collection
    {
        return $this->filtered($start, $end, $filters)
            ->with(['employeeBiometric', 'plottingSchedule'])
            ->orderBy('employee_name')
            ->orderBy('work_date')
            ->get();
    }

    public function stats(CarbonInterface $start, CarbonInterface $end, array $filters): array
    {
        // Aliases get a prefix: some keys (e.g. "leave") are reserved words in MySQL.
        $query = $this->filtered($start, $end, $filters)->selectRaw('COUNT(*) as stat_total');
        foreach (self::COUNTS as $key => $condition) {
            $query->selectRaw("COALESCE(SUM(CASE WHEN {$condition} THEN 1 ELSE 0 END), 0) as stat_{$key}");
        }
        foreach (self::SUMS as $key => $column) {
            $query->selectRaw("COALESCE(SUM({$column}), 0) as stat_{$key}");
        }

        $row = $query->toBase()->first();
        $value = fn (string $key): mixed => $row?->{"stat_{$key}"} ?? 0;
        $stats = ['total' => (int) $value('total')];
        foreach (array_keys(self::COUNTS) as $key) {
            $stats[$key] = (int) $value($key);
        }
        foreach (self::SUMS as $key => $column) {
            $stats[$key] = str_ends_with($column, '_minutes') ? (int) $value($key) : (float) $value($key);
        }

        return $stats;
    }

    public function employeeIdsWithRows(CarbonInterface $start, CarbonInterface $end, Collection $employeeBiometricIds): Collection
    {
        return DailyAttendanceSummary::query()
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('employee_biometric_id', $employeeBiometricIds)
            ->distinct()
            ->pluck('employee_biometric_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();
    }

    public function forEmployeeOn(int $employeeBiometricId, string $date): ?DailyAttendanceSummary
    {
        return DailyAttendanceSummary::query()
            ->where('employee_biometric_id', $employeeBiometricId)
            ->whereDate('work_date', $date)
            ->first();
    }

    public function forPayrollItem(string $start, string $end, PayrollItem $item): Collection
    {
        return DailyAttendanceSummary::query()
            ->with(['employeeBiometric', 'plottingSchedule'])
            ->whereBetween('work_date', [$start, $end])
            ->when(
                ! empty($item->employee_biometric_id),
                fn (Builder $query) => $query->where('employee_biometric_id', (int) $item->employee_biometric_id),
                fn (Builder $query) => $query->where(function (Builder $query) use ($item): void {
                    foreach (['biometric_employee_id', 'employee_no', 'employee_name'] as $column) {
                        if (! empty($item->{$column})) {
                            $query->orWhere($column, $item->{$column});
                        }
                    }
                }),
            )
            ->orderBy('work_date')
            ->get();
    }

    /**
     * @param  array{search: string, status: string, day_type: string, group_name: string}  $filters
     * @return Builder<DailyAttendanceSummary>
     */
    private function filtered(CarbonInterface $start, CarbonInterface $end, array $filters): Builder
    {
        $search = $filters['search'];
        $group = trim($filters['group_name']);

        return DailyAttendanceSummary::query()
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->whereHas('employeeBiometric', function (Builder $person) use ($group): void {
                /** @var Builder<EmployeeBiometric> $person */
                $person->payrollActive();
                if ($group !== '') {
                    $person->where('group_name', $group);
                }
            })
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('employee_name', 'like', "%{$search}%")
                ->orWhere('employee_no', 'like', "%{$search}%")
                ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                ->orWhereHas('employeeBiometric', fn (Builder $person) => $person
                    ->where('display_name', 'like', "%{$search}%")
                    ->orWhere('display_employee_no', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%"))
                ->orWhere('attendance_status', 'like', "%{$search}%")
                ->orWhere('shift_name', 'like', "%{$search}%")
                ->orWhere('holiday_name', 'like', "%{$search}%")
                ->orWhere('holiday_type', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%")
                ->orWhere('schedule_remarks', 'like', "%{$search}%")))
            ->when($filters['status'] !== '', fn (Builder $query) => match ($filters['status']) {
                'needs_review' => $query->whereIn('attendance_status', self::NEEDS_REVIEW),
                'payable' => $query->where('payable_days', '>', 0),
                default => $query->where('attendance_status', $filters['status']),
            })
            ->when($filters['day_type'] !== '', fn (Builder $query) => match ($filters['day_type']) {
                'holiday' => $query->where('is_holiday', true),
                'holiday_paid' => $query->where('is_holiday', true)->where('payable_days', '>', 0),
                'holiday_unpaid' => $query->where('attendance_status', 'holiday_unpaid'),
                'rest_day' => $query->where('is_rest_day', true),
                'leave' => $query->where('is_leave', true),
                'adjustment' => $query->where('has_adjustment', true),
                'regular' => $query->where('is_holiday', false)->where('is_rest_day', false)->where('is_leave', false),
                'regular_shift' => $query->where('shift_name', 'like', '%Regular%'),
                'flexible_shift' => $query->where('shift_name', 'like', '%Flexible%'),
                'needs_review' => $query->whereIn('attendance_status', self::NEEDS_REVIEW),
                default => $query,
            });
    }
}
