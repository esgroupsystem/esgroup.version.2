<?php

namespace App\Services\Payroll;

use App\Models\DailyAttendanceSummary;
use App\Models\EmployeePlottingSchedule;
use App\Models\Holiday;
use App\Models\MirasolBiometricsLog;
use App\Models\PayrollAttendanceAdjustment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DailyAttendanceSummaryService
{
    private const FULL_DAY_MINUTES = 540; // 9 hours

    private const HALF_DAY_MINUTES = 270; // 4.5 hours

    private const FULL_DAY_PAYABLE_DAYS = 1.00;

    private const HALF_DAY_PAYABLE_DAYS = 0.50;

    private const FULL_DAY_PAYABLE_HOURS = 9.00;

    private const HALF_DAY_PAYABLE_HOURS = 4.50;

    public function buildForDate(string|Carbon $date): void
    {
        $workDate = $date instanceof Carbon
            ? $date->copy()->timezone('Asia/Manila')->startOfDay()
            : Carbon::parse($date, 'Asia/Manila')->startOfDay();

        $people = $this->collectPeopleForDate($workDate);

        foreach ($people as $person) {
            $this->buildForPersonDate($person, $workDate);
        }
    }

    public function buildForPeriod(string|Carbon $startDate, string|Carbon $endDate): void
    {
        $start = $startDate instanceof Carbon
            ? $startDate->copy()->timezone('Asia/Manila')->startOfDay()
            : Carbon::parse($startDate, 'Asia/Manila')->startOfDay();

        $end = $endDate instanceof Carbon
            ? $endDate->copy()->timezone('Asia/Manila')->startOfDay()
            : Carbon::parse($endDate, 'Asia/Manila')->startOfDay();

        $current = $start->copy();

        while ($current->lte($end)) {
            $this->buildForDate($current);
            $current->addDay();
        }
    }

    protected function collectPeopleForDate(Carbon $workDate): Collection
    {
        $people = collect();

        /*
         * Include all plotted employees.
         * This supports permanent schedules where work_date may be NULL.
         */
        EmployeePlottingSchedule::query()
            ->get()
            ->each(function ($row) use ($people) {
                $person = [
                    'biometric_employee_id' => $this->cleanString($row->biometric_employee_id),
                    'employee_no' => $this->cleanString($row->employee_no),
                    'employee_name' => $this->cleanString($row->employee_name),
                ];

                $people->put($this->makePersonKey($person), $person);
            });

        /*
         * Include employees with biometric logs on the work date.
         */
        MirasolBiometricsLog::query()
            ->whereDate('check_time', $workDate->toDateString())
            ->get()
            ->each(function ($row) use ($people) {
                $person = [
                    'biometric_employee_id' => $this->cleanString($row->employee_id),
                    'employee_no' => $this->cleanString($row->employee_no),
                    'employee_name' => $this->cleanString($row->employee_name),
                ];

                $people->put($this->makePersonKey($person), $person);
            });

        /*
         * Include employees with manual attendance adjustment.
         */
        PayrollAttendanceAdjustment::query()
            ->whereDate('work_date', $workDate->toDateString())
            ->get()
            ->each(function ($row) use ($people) {
                $person = [
                    'biometric_employee_id' => $this->cleanString($row->biometric_employee_id),
                    'employee_no' => $this->cleanString($row->employee_no),
                    'employee_name' => $this->cleanString($row->employee_name),
                ];

                $people->put($this->makePersonKey($person), $person);
            });

        return $people
            ->filter(fn ($person) => ! empty($person['employee_no']) || ! empty($person['biometric_employee_id']))
            ->values();
    }

    public function buildForPersonDate(array $person, string|Carbon $date): DailyAttendanceSummary
    {
        $workDate = $date instanceof Carbon
            ? $date->copy()->timezone('Asia/Manila')->startOfDay()
            : Carbon::parse($date, 'Asia/Manila')->startOfDay();

        $schedule = $this->resolveScheduleForPersonDate($person, $workDate);

        $logs = MirasolBiometricsLog::query()
            ->whereDate('check_time', $workDate->toDateString())
            ->where(function ($query) use ($person) {
                $this->applyLogPersonMatch($query, $person);
            })
            ->orderBy('check_time')
            ->get();

        $adjustment = PayrollAttendanceAdjustment::query()
            ->whereDate('work_date', $workDate->toDateString())
            ->where(function ($query) use ($person) {
                $this->applyAdjustmentPersonMatch($query, $person);
            })
            ->latest('id')
            ->first();

        $holiday = $this->resolveHoliday($workDate);

        return $this->storeSummary(
            $person,
            $workDate,
            $schedule,
            $logs,
            $adjustment,
            $holiday
        );
    }

    protected function resolveScheduleForPersonDate(array $person, Carbon $workDate): ?EmployeePlottingSchedule
    {
        /*
         * Priority 1: exact date schedule.
         * Priority 2: permanent schedule where work_date is NULL.
         * Priority 3: latest schedule for that employee.
         */
        $exactSchedule = EmployeePlottingSchedule::query()
            ->whereDate('work_date', $workDate->toDateString())
            ->where(function ($query) use ($person) {
                $this->applySchedulePersonMatch($query, $person);
            })
            ->latest('updated_at')
            ->first();

        if ($exactSchedule) {
            return $exactSchedule;
        }

        $permanentSchedule = EmployeePlottingSchedule::query()
            ->whereNull('work_date')
            ->where(function ($query) use ($person) {
                $this->applySchedulePersonMatch($query, $person);
            })
            ->latest('updated_at')
            ->first();

        if ($permanentSchedule) {
            return $permanentSchedule;
        }

        return EmployeePlottingSchedule::query()
            ->where(function ($query) use ($person) {
                $this->applySchedulePersonMatch($query, $person);
            })
            ->latest('updated_at')
            ->first();
    }

    protected function resolveHoliday(Carbon $workDate)
    {
        if (! class_exists(Holiday::class)) {
            return null;
        }

        return Holiday::query()
            ->when($this->columnExists('holidays', 'is_active'), function ($query) {
                $query->where('is_active', true);
            })
            ->where(function ($query) use ($workDate) {
                if ($this->columnExists('holidays', 'observed_date')) {
                    $query->whereDate('observed_date', $workDate->toDateString());
                } elseif ($this->columnExists('holidays', 'holiday_date')) {
                    $query->whereDate('holiday_date', $workDate->toDateString());
                } elseif ($this->columnExists('holidays', 'date')) {
                    $query->whereDate('date', $workDate->toDateString());
                } elseif ($this->columnExists('holidays', 'work_date')) {
                    $query->whereDate('work_date', $workDate->toDateString());
                }
            })
            ->first();
    }

    protected function storeSummary(
        array $person,
        Carbon $workDate,
        ?EmployeePlottingSchedule $schedule,
        Collection $logs,
        ?PayrollAttendanceAdjustment $adjustment,
        $holiday
    ): DailyAttendanceSummary {
        $firstLog = $logs->first();
        $lastLog = $logs->last();

        $actualTimeIn = $firstLog?->check_time
            ? Carbon::parse($firstLog->check_time, 'Asia/Manila')
            : null;

        $actualTimeOut = $logs->count() >= 2 && $lastLog?->check_time
            ? Carbon::parse($lastLog->check_time, 'Asia/Manila')
            : null;

        $rawLogCount = $logs->count();
        $hasBiometrics = $rawLogCount > 0;

        $shiftName = $schedule?->shift_name ?: 'Regular Shift';
        $scheduleStatus = $schedule?->status ?: 'scheduled';
        $scheduleRemarks = $schedule?->remarks;
        $dayOff = $schedule?->day_off;
        $graceMinutes = (int) ($schedule?->grace_minutes ?? 15);

        $scheduledTimeIn = $this->normalizeTime($schedule?->time_in);
        $scheduledTimeOut = $this->normalizeTime($schedule?->time_out);

        /*
         * Day off from plotting schedule automatically becomes rest day.
         */
        if (! empty($dayOff) && strtolower($dayOff) === strtolower($workDate->format('l'))) {
            $scheduleStatus = 'rest_day';
        }

        $isFlexible = $this->isFlexibleShift($shiftName);
        $isRegular = ! $isFlexible;

        $isHoliday = ! is_null($holiday);
        $isRestDay = $scheduleStatus === 'rest_day';
        $isLeave = $scheduleStatus === 'leave';

        $holidayName = $holiday?->name;
        $holidayType = $holiday?->holiday_type;

        $hasAdjustment = ! is_null($adjustment);
        $adjustmentType = $adjustment?->adjustment_type;
        $adjustmentRemarks = $adjustment?->remarks;
        $adjustmentReason = $adjustment?->reason;
        $adjustmentIsPaid = (bool) ($adjustment?->is_paid ?? false);
        $ignoreLate = (bool) ($adjustment?->ignore_late ?? false);
        $ignoreUndertime = (bool) ($adjustment?->ignore_undertime ?? false);

        $remarks = [];
        $lateMinutes = 0;
        $undertimeMinutes = 0;
        $overtimeMinutes = 0;
        $workedMinutes = 0;
        $payableDays = 0.00;
        $payableHours = 0.00;
        $attendanceStatus = 'absent';

        if (! empty($scheduleRemarks)) {
            $remarks[] = 'Schedule: '.$scheduleRemarks;
        }

        if (! empty($adjustmentReason)) {
            $remarks[] = 'Adjustment Reason: '.$adjustmentReason;
        }

        /*
         * Apply adjusted actual time if adjustment provides time.
         */
        if ($adjustment) {
            if (! empty($adjustment->adjusted_time_in)) {
                $actualTimeIn = Carbon::parse(
                    $workDate->toDateString().' '.$adjustment->adjusted_time_in,
                    'Asia/Manila'
                );
                $remarks[] = 'Adjusted actual time in applied.';
            }

            if (! empty($adjustment->adjusted_time_out)) {
                $actualTimeOut = Carbon::parse(
                    $workDate->toDateString().' '.$adjustment->adjusted_time_out,
                    'Asia/Manila'
                );
                $remarks[] = 'Adjusted actual time out applied.';
            }

            if (! empty($adjustment->adjusted_day_type)) {
                $scheduleStatus = $adjustment->adjusted_day_type;
                $isRestDay = $scheduleStatus === 'rest_day';
                $isLeave = $scheduleStatus === 'leave';
                $remarks[] = 'Adjusted day type applied.';
            }
        }

        /*
         * Compute worked minutes only if actual out is valid and greater than actual in.
         */
        if ($actualTimeIn && $actualTimeOut && $actualTimeOut->gt($actualTimeIn)) {
            $workedMinutes = $actualTimeIn->diffInMinutes($actualTimeOut);
        }

        /*
         * Automatic Half Day Rule:
         * - Has Time In but no Time Out
         * - Has Time In and Time Out but same or invalid sequence
         */
        $isAutomaticHalfDay = $this->isAutomaticHalfDay($actualTimeIn, $actualTimeOut);

        if ($isHoliday) {
            if ($hasBiometrics) {
                $attendanceStatus = 'holiday_worked';
                $payableDays = self::FULL_DAY_PAYABLE_DAYS;
                $payableHours = self::FULL_DAY_PAYABLE_HOURS;
            } else {
                $attendanceStatus = 'holiday';
                $payableDays = 0.00;
                $payableHours = 0.00;
            }
        } elseif ($isLeave) {
            $attendanceStatus = 'leave';
            $payableDays = 1.00;
            $payableHours = self::FULL_DAY_PAYABLE_HOURS;
        } elseif ($isRestDay) {
            if ($hasBiometrics) {
                $attendanceStatus = 'rest_day_worked';
                $payableDays = self::FULL_DAY_PAYABLE_DAYS;
                $payableHours = self::FULL_DAY_PAYABLE_HOURS;
            } else {
                $attendanceStatus = 'rest_day';
                $payableDays = 0.00;
                $payableHours = 0.00;
            }
        } elseif (! $hasBiometrics) {
            $attendanceStatus = 'absent';
            $payableDays = 0.00;
            $payableHours = 0.00;
        } elseif ($isAutomaticHalfDay) {
            $attendanceStatus = 'half_day';
            $workedMinutes = self::HALF_DAY_MINUTES;
            $undertimeMinutes = self::FULL_DAY_MINUTES - self::HALF_DAY_MINUTES;
            $payableDays = self::HALF_DAY_PAYABLE_DAYS;
            $payableHours = self::HALF_DAY_PAYABLE_HOURS;
            $remarks[] = 'Automatic half day: no time out or time in and time out are the same.';
        } elseif ($isFlexible) {
            if ($workedMinutes >= self::FULL_DAY_MINUTES) {
                $attendanceStatus = 'present';
                $payableDays = self::FULL_DAY_PAYABLE_DAYS;
                $payableHours = self::FULL_DAY_PAYABLE_HOURS;
            } else {
                $attendanceStatus = 'undertime';
                $undertimeMinutes = self::FULL_DAY_MINUTES - $workedMinutes;
                $payableDays = max(0, round($workedMinutes / self::FULL_DAY_MINUTES, 2));
                $payableHours = round($workedMinutes / 60, 2);
            }
        } elseif ($isRegular) {
            [$lateMinutes, $undertimeMinutes] = $this->computeRegularShiftDeductions(
                $workDate,
                $scheduledTimeIn,
                $scheduledTimeOut,
                $actualTimeIn,
                $actualTimeOut,
                $graceMinutes
            );

            if ($ignoreLate) {
                $lateMinutes = 0;
                $remarks[] = 'Late ignored by adjustment.';
            }

            if ($ignoreUndertime) {
                $undertimeMinutes = 0;
                $remarks[] = 'Undertime ignored by adjustment.';
            }

            if ($lateMinutes > 0 && $undertimeMinutes > 0) {
                $attendanceStatus = 'late_undertime';
            } elseif ($lateMinutes > 0) {
                $attendanceStatus = 'late';
            } elseif ($undertimeMinutes > 0) {
                $attendanceStatus = 'undertime';
            } else {
                $attendanceStatus = 'present';
            }

            $payableDays = self::FULL_DAY_PAYABLE_DAYS;
            $payableHours = self::FULL_DAY_PAYABLE_HOURS;
        }

        /*
         * Paid adjustment can force payable day.
         */
        if ($hasAdjustment && $adjustmentIsPaid && in_array($attendanceStatus, ['absent', 'incomplete_log'], true)) {
            $attendanceStatus = 'adjusted_present';
            $payableDays = self::FULL_DAY_PAYABLE_DAYS;
            $payableHours = self::FULL_DAY_PAYABLE_HOURS;
            $remarks[] = 'Paid adjustment applied.';
        }

        /*
         * Overtime calculation, only when worked minutes exceed 9 hours.
         */
        if ($workedMinutes > self::FULL_DAY_MINUTES) {
            $overtimeMinutes = $workedMinutes - self::FULL_DAY_MINUTES;
        }

        $summaryKeys = [
            'work_date' => $workDate->toDateString(),
        ];

        if (! empty($person['employee_no'])) {
            $summaryKeys['employee_no'] = $this->cleanString($person['employee_no']);
        } else {
            $summaryKeys['biometric_employee_id'] = $this->cleanString($person['biometric_employee_id']);
        }

        return DailyAttendanceSummary::updateOrCreate(
            $summaryKeys,
            [
                'biometric_employee_id' => $this->cleanString($person['biometric_employee_id']),
                'employee_no' => $this->cleanString($person['employee_no']),
                'employee_name' => $this->cleanString($person['employee_name']),

                'shift_name' => $shiftName,
                'schedule_status' => $scheduleStatus,
                'scheduled_time_in' => $scheduledTimeIn,
                'scheduled_time_out' => $scheduledTimeOut,
                'grace_minutes' => $graceMinutes,
                'schedule_remarks' => $scheduleRemarks,

                'actual_time_in' => $actualTimeIn,
                'actual_time_out' => $actualTimeOut,

                'late_minutes' => max(0, (int) $lateMinutes),
                'undertime_minutes' => max(0, (int) $undertimeMinutes),
                'worked_minutes' => max(0, (int) $workedMinutes),
                'overtime_minutes' => max(0, (int) $overtimeMinutes),

                'attendance_status' => $attendanceStatus,

                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayName,
                'holiday_type' => $holidayType,

                'is_rest_day' => $isRestDay,
                'is_leave' => $isLeave,

                'has_adjustment' => $hasAdjustment,
                'adjustment_type' => $adjustmentType,
                'adjustment_remarks' => $adjustmentRemarks,

                'payable_days' => $payableDays,
                'payable_hours' => $payableHours,

                'remarks' => implode(' ', array_filter($remarks)),
            ]
        );
    }

    protected function computeRegularShiftDeductions(
        Carbon $workDate,
        ?string $scheduledTimeIn,
        ?string $scheduledTimeOut,
        ?Carbon $actualTimeIn,
        ?Carbon $actualTimeOut,
        int $graceMinutes
    ): array {
        $lateMinutes = 0;
        $undertimeMinutes = 0;

        if (! $actualTimeIn || ! $actualTimeOut) {
            return [$lateMinutes, $undertimeMinutes];
        }

        if (empty($scheduledTimeIn) || empty($scheduledTimeOut)) {
            return [$lateMinutes, $undertimeMinutes];
        }

        $scheduledIn = Carbon::parse($workDate->toDateString().' '.$scheduledTimeIn, 'Asia/Manila');
        $scheduledOut = Carbon::parse($workDate->toDateString().' '.$scheduledTimeOut, 'Asia/Manila');

        if ($scheduledOut->lessThanOrEqualTo($scheduledIn)) {
            $scheduledOut->addDay();
        }

        $allowedIn = $scheduledIn->copy()->addMinutes($graceMinutes);

        if ($actualTimeIn->gt($allowedIn)) {
            $lateMinutes = $allowedIn->diffInMinutes($actualTimeIn);
        }

        if ($actualTimeOut->lt($scheduledOut)) {
            $undertimeMinutes = $actualTimeOut->diffInMinutes($scheduledOut);
        }

        return [$lateMinutes, $undertimeMinutes];
    }

    protected function isAutomaticHalfDay(?Carbon $actualTimeIn, ?Carbon $actualTimeOut): bool
    {
        if (! $actualTimeIn) {
            return false;
        }

        if (! $actualTimeOut) {
            return true;
        }

        return $actualTimeOut->lessThanOrEqualTo($actualTimeIn);
    }

    protected function normalizeTime($time): ?string
    {
        if (empty($time)) {
            return null;
        }

        return Carbon::parse($time)->format('H:i:s');
    }

    protected function isFlexibleShift(?string $shiftName): bool
    {
        return str_contains(strtolower((string) $shiftName), 'flexible');
    }

    protected function applySchedulePersonMatch(Builder $query, array $person): void
    {
        $employeeNo = $this->cleanString($person['employee_no'] ?? null);
        $biometricEmployeeId = $this->cleanString($person['biometric_employee_id'] ?? null);

        $query->where(function ($q) use ($employeeNo, $biometricEmployeeId) {
            if ($employeeNo !== '') {
                $q->orWhereRaw('TRIM(employee_no) = ?', [$employeeNo]);
            }

            if ($biometricEmployeeId !== '') {
                $q->orWhereRaw('TRIM(biometric_employee_id) = ?', [$biometricEmployeeId]);
            }
        });
    }

    protected function applyLogPersonMatch(Builder $query, array $person): void
    {
        $employeeNo = $this->cleanString($person['employee_no'] ?? null);
        $biometricEmployeeId = $this->cleanString($person['biometric_employee_id'] ?? null);

        $query->where(function ($q) use ($employeeNo, $biometricEmployeeId) {
            if ($employeeNo !== '') {
                $q->orWhereRaw('TRIM(employee_no) = ?', [$employeeNo]);
            }

            if ($biometricEmployeeId !== '') {
                $q->orWhereRaw('TRIM(employee_id) = ?', [$biometricEmployeeId]);
            }
        });
    }

    protected function applyAdjustmentPersonMatch(Builder $query, array $person): void
    {
        $employeeNo = $this->cleanString($person['employee_no'] ?? null);
        $biometricEmployeeId = $this->cleanString($person['biometric_employee_id'] ?? null);

        $query->where(function ($q) use ($employeeNo, $biometricEmployeeId) {
            if ($employeeNo !== '') {
                $q->orWhereRaw('TRIM(employee_no) = ?', [$employeeNo]);
            }

            if ($biometricEmployeeId !== '') {
                $q->orWhereRaw('TRIM(biometric_employee_id) = ?', [$biometricEmployeeId]);
            }
        });
    }

    protected function makePersonKey(array $person): string
    {
        $employeeNo = $this->cleanString($person['employee_no'] ?? null);
        $biometricEmployeeId = $this->cleanString($person['biometric_employee_id'] ?? null);

        if ($employeeNo !== '') {
            return 'EMPLOYEE_NO:'.$employeeNo;
        }

        if ($biometricEmployeeId !== '') {
            return 'BIOMETRIC_ID:'.$biometricEmployeeId;
        }

        return 'UNKNOWN:'.md5(json_encode($person));
    }

    protected function cleanString($value): string
    {
        return trim((string) ($value ?? ''));
    }

    protected function columnExists(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
