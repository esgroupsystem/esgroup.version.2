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

    private const REGULAR_HOLIDAY_WORKED_PAY_DAYS = 2.00;

    private const SPECIAL_HOLIDAY_WORKED_PAY_DAYS = 1.30;

    private array $columnCache = [];

    public function buildForDate(string|Carbon $date): void
    {
        $workDate = $this->asManilaDate($date);

        /*
         * Delete only this date before rebuilding.
         * This avoids one very long database transaction for the whole cutoff.
         */
        DailyAttendanceSummary::query()
            ->whereDate('work_date', $workDate->toDateString())
            ->delete();

        $people = $this->collectPeopleForDate($workDate);

        foreach ($people as $person) {
            $this->buildForPersonDate($person, $workDate);
        }
    }

    public function buildForPeriod(string|Carbon $startDate, string|Carbon $endDate): void
    {
        $start = $this->asManilaDate($startDate);
        $end = $this->asManilaDate($endDate);

        @ini_set('max_execution_time', '300');
        @set_time_limit(300);

        $current = $start->copy();

        while ($current->lte($end)) {
            /*
             * Refresh timeout per date.
             */
            @set_time_limit(300);

            $this->buildForDate($current);

            $current->addDay();
        }
    }

    protected function buildDateRows(Carbon $workDate): void
    {
        $people = $this->collectPeopleForDate($workDate);

        foreach ($people as $person) {
            $this->buildForPersonDate($person, $workDate);
        }
    }

    protected function collectPeopleForDate(Carbon $workDate): Collection
    {
        $people = collect();

        /*
         * 1. Include all employees with plotted schedule.
         * Since your plotting is now permanent, work_date can be NULL.
         */
        EmployeePlottingSchedule::query()
            ->where(function ($query) {
                $query->whereNotNull('employee_no')
                    ->orWhereNotNull('biometric_employee_id')
                    ->orWhereNotNull('crosschex_id');
            })
            ->get()
            ->each(function ($row) use ($people) {
                $this->putPerson($people, [
                    'crosschex_id' => $this->cleanString($row->crosschex_id ?? null),
                    'biometric_employee_id' => $this->cleanString($row->biometric_employee_id ?? null),
                    'employee_no' => $this->cleanString($row->employee_no ?? null),
                    'employee_name' => $this->cleanString($row->employee_name ?? null),
                ]);
            });

        /*
         * 2. Include employees with biometrics on the work date.
         * These will match against plotted schedule using employee_no OR biometric_employee_id OR employee_id.
         */
        MirasolBiometricsLog::query()
            ->whereDate($this->biometricTimeColumn(), $workDate->toDateString())
            ->get()
            ->each(function ($row) use ($people) {
                $this->putPerson($people, [
                    'crosschex_id' => $this->cleanString($row->crosschex_id ?? null),
                    'biometric_employee_id' => $this->cleanString($row->employee_id ?? null),
                    'employee_no' => $this->cleanString($row->employee_no ?? null),
                    'employee_name' => $this->cleanString($row->employee_name ?? null),
                ]);
            });

        /*
         * 3. Include manual adjustments.
         */
        PayrollAttendanceAdjustment::query()
            ->whereDate('work_date', $workDate->toDateString())
            ->get()
            ->each(function ($row) use ($people) {
                $this->putPerson($people, [
                    'crosschex_id' => $this->cleanString($row->crosschex_id ?? null),
                    'biometric_employee_id' => $this->cleanString($row->biometric_employee_id ?? null),
                    'employee_no' => $this->cleanString($row->employee_no ?? null),
                    'employee_name' => $this->cleanString($row->employee_name ?? null),
                ]);
            });

        return $people
            ->filter(fn (array $person) => count($this->personIdentityValues($person)) > 0)
            ->values();
    }

    protected function putPerson(Collection $people, array $newPerson): void
    {
        $newIdentities = $this->personIdentityValues($newPerson);

        if (empty($newIdentities)) {
            return;
        }

        $existingKey = null;

        foreach ($people as $key => $existingPerson) {
            $existingIdentities = $this->personIdentityValues($existingPerson);

            if (! empty(array_intersect($newIdentities, $existingIdentities))) {
                $existingKey = $key;
                break;
            }
        }

        if ($existingKey === null) {
            $people->put($this->makePersonKey($newPerson), $newPerson);

            return;
        }

        $existingPerson = $people->get($existingKey);

        $people->put($existingKey, [
            'crosschex_id' => $existingPerson['crosschex_id'] ?: ($newPerson['crosschex_id'] ?? ''),
            'biometric_employee_id' => $existingPerson['biometric_employee_id'] ?: ($newPerson['biometric_employee_id'] ?? ''),
            'employee_no' => $existingPerson['employee_no'] ?: ($newPerson['employee_no'] ?? ''),
            'employee_name' => $existingPerson['employee_name'] ?: ($newPerson['employee_name'] ?? ''),
        ]);
    }

    public function buildForPersonDate(array $person, string|Carbon $date): DailyAttendanceSummary
    {
        $workDate = $this->asManilaDate($date);

        $schedule = $this->resolveScheduleForPersonDate($person, $workDate);
        $logs = $this->logsForPersonDate($person, $workDate, $schedule);

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
        $table = (new EmployeePlottingSchedule)->getTable();

        $baseQuery = EmployeePlottingSchedule::query()
            ->where(function ($query) use ($person) {
                $this->applySchedulePersonMatch($query, $person);
            });

        /*
         * Priority 1:
         * Permanent plotted schedule.
         * This is your current setup: work_date = NULL.
         */
        if ($this->columnExists($table, 'work_date')) {
            $permanentSchedule = (clone $baseQuery)
                ->whereNull('work_date')
                ->latest('updated_at')
                ->latest('id')
                ->first();

            if ($permanentSchedule) {
                return $permanentSchedule;
            }

            /*
             * Priority 2:
             * Exact old date-based schedule fallback.
             */
            $exactDateSchedule = (clone $baseQuery)
                ->whereDate('work_date', $workDate->toDateString())
                ->latest('updated_at')
                ->latest('id')
                ->first();

            if ($exactDateSchedule) {
                return $exactDateSchedule;
            }
        }

        /*
         * Priority 3:
         * Last fallback for old rows.
         */
        return (clone $baseQuery)
            ->latest('updated_at')
            ->latest('id')
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
        $rawLogCount = $logs->count();
        $hasRawBiometrics = $rawLogCount > 0;

        $firstLog = $logs->first();
        $lastLog = $logs->last();

        $biometricTimeColumn = $this->biometricTimeColumn();

        $actualTimeIn = $firstLog?->{$biometricTimeColumn}
            ? Carbon::parse($firstLog->{$biometricTimeColumn}, 'Asia/Manila')
            : null;

        $actualTimeOut = $rawLogCount >= 2 && $lastLog?->{$biometricTimeColumn}
            ? Carbon::parse($lastLog->{$biometricTimeColumn}, 'Asia/Manila')
            : null;

        if ($actualTimeIn && $actualTimeOut && $actualTimeOut->lessThanOrEqualTo($actualTimeIn)) {
            $actualTimeOut->addDay();
        }

        $shiftName = $schedule?->shift_name ?: null;
        $scheduleStatus = $this->cleanString($schedule?->status) ?: 'scheduled';
        $scheduleRemarks = $schedule?->remarks;
        $dayOff = $schedule?->day_off;
        $graceMinutes = (int) ($schedule?->grace_minutes ?? 15);

        $scheduledTimeIn = $this->normalizeTime($schedule?->time_in);
        $scheduledTimeOut = $this->normalizeTime($schedule?->time_out);

        $isHoliday = ! is_null($holiday);
        $holidayName = $this->holidayName($holiday);
        $holidayType = $this->holidayType($holiday);

        $hasAdjustment = ! is_null($adjustment);
        $adjustmentType = $adjustment?->adjustment_type;
        $adjustmentRemarks = $adjustment?->remarks;
        $adjustmentReason = $adjustment?->reason;
        $adjustmentIsPaid = $this->adjustmentQualifiesForPay($adjustment);

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

        if (! $schedule) {
            $remarks[] = 'No plotted permanent schedule found. Please check Permanent Plotting Schedule.';
        }

        if (! empty($scheduleRemarks)) {
            $remarks[] = 'Schedule: '.$scheduleRemarks;
        }

        if (! empty($adjustmentReason)) {
            $remarks[] = 'Adjustment Reason: '.$adjustmentReason;
        }

        if ($hasRawBiometrics) {
            $remarks[] = 'Biometrics logs found: '.$rawLogCount.'.';
        }

        /*
         * Manual adjustment can replace actual time in/out.
         */
        if ($adjustment) {
            if (! empty($adjustment->adjusted_time_in)) {
                $actualTimeIn = Carbon::parse(
                    $workDate->toDateString().' '.$adjustment->adjusted_time_in,
                    'Asia/Manila'
                );

                $remarks[] = 'Adjusted time in applied.';
            }

            if (! empty($adjustment->adjusted_time_out)) {
                $actualTimeOut = Carbon::parse(
                    $workDate->toDateString().' '.$adjustment->adjusted_time_out,
                    'Asia/Manila'
                );

                $remarks[] = 'Adjusted time out applied.';
            }

            if ($actualTimeIn && $actualTimeOut && $actualTimeOut->lessThanOrEqualTo($actualTimeIn)) {
                $actualTimeOut->addDay();
            }

            if (! empty($adjustment->adjusted_day_type)) {
                $scheduleStatus = $adjustment->adjusted_day_type;
                $remarks[] = 'Adjusted day type applied.';
            }
        }

        $isRestDay = $this->scheduleIndicatesRestDay($scheduleStatus, $dayOff, $workDate);
        $isLeave = $this->scheduleIndicatesLeave($scheduleStatus);

        if ($isRestDay) {
            $scheduleStatus = 'rest_day';
        }

        if ($isLeave) {
            $scheduleStatus = 'leave';
        }

        $isFlexible = $this->isFlexibleShift($shiftName);
        $hasValidInOut = $actualTimeIn && $actualTimeOut && $actualTimeOut->gt($actualTimeIn);
        $hasAttendanceProof = $hasRawBiometrics || $hasAdjustment || $hasValidInOut;

        if ($hasValidInOut) {
            $workedMinutes = $actualTimeIn->diffInMinutes($actualTimeOut);
        }

        $isAutomaticHalfDay = $this->isAutomaticHalfDay($actualTimeIn, $actualTimeOut);

        if ($isHoliday) {
            [$holidayWorkedPayDays, $holidayRateLabel] = $this->holidayWorkedPayDays($holidayType);
            $holidayQualified = $this->isHolidayPayQualified($person, $workDate, $adjustment);

            if ($hasValidInOut) {
                $attendanceStatus = 'holiday_worked';
                $payableDays = $holidayWorkedPayDays;
                $payableHours = round(self::FULL_DAY_PAYABLE_HOURS * $holidayWorkedPayDays, 2);

                $remarks[] = 'Holiday worked rate applied: '.$holidayRateLabel.'.';
            } elseif ($isAutomaticHalfDay) {
                $attendanceStatus = 'half_day';

                $workedMinutes = self::HALF_DAY_MINUTES;
                $lateMinutes = 0;
                $undertimeMinutes = self::FULL_DAY_MINUTES - self::HALF_DAY_MINUTES;

                $payableDays = self::HALF_DAY_PAYABLE_DAYS;
                $payableHours = self::HALF_DAY_PAYABLE_HOURS;

                $remarks[] = 'No time out. Half day paid based on company policy.';
            } elseif ($holidayQualified || $adjustmentIsPaid) {
                $attendanceStatus = 'holiday';
                $payableDays = self::FULL_DAY_PAYABLE_DAYS;
                $payableHours = self::FULL_DAY_PAYABLE_HOURS;

                $remarks[] = 'Paid holiday. Before/after holiday rule qualified.';
            } else {
                $attendanceStatus = 'holiday_unpaid';
                $payableDays = 0.00;
                $payableHours = 0.00;

                $remarks[] = 'Holiday unpaid. Employee has no qualifying record before and after holiday.';
            }
        } elseif ($isLeave) {
            $attendanceStatus = 'leave';
            $payableDays = self::FULL_DAY_PAYABLE_DAYS;
            $payableHours = self::FULL_DAY_PAYABLE_HOURS;

            $remarks[] = 'Paid leave/day type applied.';
        } elseif ($isRestDay) {
            $payableDays = self::FULL_DAY_PAYABLE_DAYS;
            $payableHours = self::FULL_DAY_PAYABLE_HOURS;

            if ($hasValidInOut) {
                $attendanceStatus = 'rest_day_worked';
                $remarks[] = 'Rest day worked. Base rest day pay retained.';
            } elseif ($isAutomaticHalfDay) {
                $attendanceStatus = 'incomplete_log';
                $remarks[] = 'Rest day has incomplete biometrics. Base rest day pay retained; please verify.';
            } else {
                $attendanceStatus = 'rest_day';
                $remarks[] = 'Paid rest day/day off.';
            }
        } elseif (! $schedule && ! $adjustmentIsPaid) {
            $attendanceStatus = 'no_schedule';
            $payableDays = 0.00;
            $payableHours = 0.00;
        } elseif (! $hasAttendanceProof) {
            $attendanceStatus = 'absent';
            $payableDays = 0.00;
            $payableHours = 0.00;
        } elseif ($isAutomaticHalfDay) {
            $attendanceStatus = 'half_day';
            $workedMinutes = self::HALF_DAY_MINUTES;
            $undertimeMinutes = self::FULL_DAY_MINUTES - self::HALF_DAY_MINUTES;
            $payableDays = self::HALF_DAY_PAYABLE_DAYS;
            $payableHours = self::HALF_DAY_PAYABLE_HOURS;

            $remarks[] = 'No time out. Half day paid based on company policy.';
        } elseif ($isFlexible) {
            /*
             * Flexible rule:
             * Full day only when complete 9 hours.
             */
            if ($workedMinutes >= self::FULL_DAY_MINUTES) {
                $attendanceStatus = 'present';
                $payableDays = self::FULL_DAY_PAYABLE_DAYS;
                $payableHours = self::FULL_DAY_PAYABLE_HOURS;

                $remarks[] = 'Flexible shift completed 9 hours.';
            } else {
                $attendanceStatus = 'undertime';
                $undertimeMinutes = max(0, self::FULL_DAY_MINUTES - $workedMinutes);
                $payableDays = max(0, round($workedMinutes / self::FULL_DAY_MINUTES, 2));
                $payableHours = round($workedMinutes / 60, 2);

                $remarks[] = 'Flexible shift below 9 hours.';
            }
        } else {
            /*
             * Fixed / Regular Shift rule.
             */
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

        if (
            $hasAdjustment
            && $adjustmentIsPaid
            && in_array($attendanceStatus, ['absent', 'incomplete_log', 'no_schedule', 'holiday_unpaid'], true)
        ) {
            $attendanceStatus = 'adjusted_present';
            $payableDays = self::FULL_DAY_PAYABLE_DAYS;
            $payableHours = self::FULL_DAY_PAYABLE_HOURS;

            $remarks[] = 'Paid adjustment forced payable attendance.';
        }

        if ($workedMinutes > self::FULL_DAY_MINUTES) {
            $overtimeMinutes = $workedMinutes - self::FULL_DAY_MINUTES;
        }

        $employeeNo = $this->cleanString($person['employee_no'] ?? null);
        $biometricEmployeeId = $this->cleanString($person['biometric_employee_id'] ?? null);

        /*
         * If biometric_employee_id is empty but employee_no is the same number used by biometrics,
         * keep employee_no as the primary key.
         */
        $summaryKeys = [
            'work_date' => $workDate->toDateString(),
        ];

        if ($employeeNo !== '') {
            $summaryKeys['employee_no'] = $employeeNo;
        } else {
            $summaryKeys['biometric_employee_id'] = $biometricEmployeeId;
        }

        return DailyAttendanceSummary::updateOrCreate(
            $summaryKeys,
            [
                'biometric_employee_id' => $biometricEmployeeId,
                'employee_no' => $employeeNo,
                'employee_name' => $this->cleanString($person['employee_name'] ?? null),

                'shift_name' => $shiftName ?: 'No Schedule',
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

                'payable_days' => round((float) $payableDays, 2),
                'payable_hours' => round((float) $payableHours, 2),

                'remarks' => implode(' ', array_filter($remarks)),
            ]
        );
    }

    protected function logsForPersonDate(
        array $person,
        Carbon $workDate,
        ?EmployeePlottingSchedule $schedule = null
    ): Collection {
        $timeColumn = $this->biometricTimeColumn();

        $start = $workDate->copy()->startOfDay();
        $end = $workDate->copy()->endOfDay();

        $scheduledTimeIn = $this->normalizeTime($schedule?->time_in);
        $scheduledTimeOut = $this->normalizeTime($schedule?->time_out);

        if ($scheduledTimeIn && $scheduledTimeOut) {
            $scheduledIn = Carbon::parse($workDate->toDateString().' '.$scheduledTimeIn, 'Asia/Manila');
            $scheduledOut = Carbon::parse($workDate->toDateString().' '.$scheduledTimeOut, 'Asia/Manila');

            if ($scheduledOut->lessThanOrEqualTo($scheduledIn)) {
                $scheduledOut->addDay();
                $end = $scheduledOut->copy()->addHours(6);
            }
        }

        return MirasolBiometricsLog::query()
            ->whereBetween($timeColumn, [$start, $end])
            ->where(function ($query) use ($person) {
                $this->applyLogPersonMatch($query, $person);
            })
            ->orderBy($timeColumn)
            ->get();
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

    protected function resolveHoliday(Carbon $workDate)
    {
        if (! class_exists(Holiday::class)) {
            return null;
        }

        $table = (new Holiday)->getTable();

        $dateColumn = null;

        foreach (['observed_date', 'holiday_date', 'date', 'work_date'] as $column) {
            if ($this->columnExists($table, $column)) {
                $dateColumn = $column;
                break;
            }
        }

        if (! $dateColumn) {
            return null;
        }

        return Holiday::query()
            ->when($this->columnExists($table, 'is_active'), function ($query) {
                $query->where('is_active', true);
            })
            ->whereDate($dateColumn, $workDate->toDateString())
            ->first();
    }

    protected function isHolidayPayQualified(
        array $person,
        Carbon $holidayDate,
        ?PayrollAttendanceAdjustment $holidayAdjustment = null
    ): bool {
        if ($this->adjustmentQualifiesForPay($holidayAdjustment)) {
            return true;
        }

        $beforeDate = $holidayDate->copy()->subDay();
        $afterDate = $holidayDate->copy()->addDay();

        return $this->isHolidayBoundaryDateQualified($person, $beforeDate)
            && $this->isHolidayBoundaryDateQualified($person, $afterDate);
    }

    protected function isHolidayBoundaryDateQualified(array $person, Carbon $date): bool
    {
        $schedule = $this->resolveScheduleForPersonDate($person, $date);
        $holiday = $this->resolveHoliday($date);

        if ($holiday) {
            return true;
        }

        if ($schedule && $this->scheduleIndicatesRestDay($schedule->status, $schedule->day_off, $date)) {
            return true;
        }

        if ($schedule && $this->scheduleIndicatesLeave($schedule->status)) {
            return true;
        }

        $adjustment = PayrollAttendanceAdjustment::query()
            ->whereDate('work_date', $date->toDateString())
            ->where(function ($query) use ($person) {
                $this->applyAdjustmentPersonMatch($query, $person);
            })
            ->latest('id')
            ->first();

        if ($adjustment) {
            return true;
        }

        $logs = $this->logsForPersonDate($person, $date, $schedule);

        if ($logs->count() < 2) {
            return false;
        }

        $timeColumn = $this->biometricTimeColumn();

        $first = Carbon::parse($logs->first()->{$timeColumn}, 'Asia/Manila');
        $last = Carbon::parse($logs->last()->{$timeColumn}, 'Asia/Manila');

        return $last->gt($first);
    }

    protected function adjustmentQualifiesForPay(?PayrollAttendanceAdjustment $adjustment): bool
    {
        if (! $adjustment) {
            return false;
        }

        if ((bool) ($adjustment->is_paid ?? false)) {
            return true;
        }

        $adjustmentType = strtolower((string) ($adjustment->adjustment_type ?? ''));
        $adjustedDayType = strtolower((string) ($adjustment->adjusted_day_type ?? ''));

        $paidKeywords = [
            'paid',
            'leave',
            'offset',
            'official',
            'ob',
            'holiday',
            'rest_day',
            'rest day',
            'day_off',
            'day off',
        ];

        foreach ($paidKeywords as $keyword) {
            if (str_contains($adjustmentType, $keyword) || str_contains($adjustedDayType, $keyword)) {
                return true;
            }
        }

        return ! empty($adjustment->adjusted_time_in) && ! empty($adjustment->adjusted_time_out);
    }

    protected function holidayWorkedPayDays(?string $holidayType): array
    {
        $type = strtolower((string) $holidayType);

        if (str_contains($type, 'special') || str_contains($type, 'non') || str_contains($type, '30')) {
            return [
                self::SPECIAL_HOLIDAY_WORKED_PAY_DAYS,
                'Special / non-regular holiday +30% = 1.30 pay units',
            ];
        }

        return [
            self::REGULAR_HOLIDAY_WORKED_PAY_DAYS,
            'Regular holiday double pay = 2.00 pay units',
        ];
    }

    protected function holidayName($holiday): ?string
    {
        if (! $holiday) {
            return null;
        }

        foreach (['name', 'holiday_name', 'title', 'description'] as $column) {
            $value = $holiday->{$column} ?? null;

            if (! empty($value)) {
                return (string) $value;
            }
        }

        return 'Holiday';
    }

    protected function holidayType($holiday): ?string
    {
        if (! $holiday) {
            return null;
        }

        foreach (['holiday_type', 'type', 'category'] as $column) {
            $value = $holiday->{$column} ?? null;

            if (! empty($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    protected function scheduleIndicatesRestDay(?string $scheduleStatus, mixed $dayOff, Carbon $workDate): bool
    {
        $status = strtolower(trim((string) $scheduleStatus));

        if (in_array($status, ['rest_day', 'rest day', 'restday', 'day_off', 'day off', 'off'], true)) {
            return true;
        }

        $dayOffText = strtolower(trim((string) $dayOff));

        if ($dayOffText === '') {
            return false;
        }

        $weekday = strtolower($workDate->format('l'));

        return str_contains($dayOffText, $weekday);
    }

    protected function scheduleIndicatesLeave(?string $scheduleStatus): bool
    {
        $status = strtolower(trim((string) $scheduleStatus));

        return in_array($status, ['leave', 'on_leave', 'paid_leave', 'sick_leave', 'vacation_leave'], true)
            || str_contains($status, 'leave');
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
        $identities = $this->personIdentityValues($person);
        $table = (new EmployeePlottingSchedule)->getTable();

        $query->where(function ($q) use ($identities, $table) {
            foreach ($identities as $identity) {
                if ($this->columnExists($table, 'employee_no')) {
                    $q->orWhereRaw('TRIM(employee_no) = ?', [$identity]);
                }

                if ($this->columnExists($table, 'biometric_employee_id')) {
                    $q->orWhereRaw('TRIM(biometric_employee_id) = ?', [$identity]);
                }

                if ($this->columnExists($table, 'crosschex_id')) {
                    $q->orWhereRaw('TRIM(crosschex_id) = ?', [$identity]);
                }
            }
        });
    }

    protected function applyLogPersonMatch(Builder $query, array $person): void
    {
        $identities = $this->personIdentityValues($person);
        $table = (new MirasolBiometricsLog)->getTable();

        $query->where(function ($q) use ($identities, $table) {
            foreach ($identities as $identity) {
                if ($this->columnExists($table, 'employee_id')) {
                    $q->orWhereRaw('TRIM(employee_id) = ?', [$identity]);
                }

                if ($this->columnExists($table, 'employee_no')) {
                    $q->orWhereRaw('TRIM(employee_no) = ?', [$identity]);
                }

                if ($this->columnExists($table, 'biometric_employee_id')) {
                    $q->orWhereRaw('TRIM(biometric_employee_id) = ?', [$identity]);
                }

                if ($this->columnExists($table, 'crosschex_id')) {
                    $q->orWhereRaw('TRIM(crosschex_id) = ?', [$identity]);
                }
            }
        });
    }

    protected function applyAdjustmentPersonMatch(Builder $query, array $person): void
    {
        $identities = $this->personIdentityValues($person);
        $table = (new PayrollAttendanceAdjustment)->getTable();

        $query->where(function ($q) use ($identities, $table) {
            foreach ($identities as $identity) {
                if ($this->columnExists($table, 'employee_no')) {
                    $q->orWhereRaw('TRIM(employee_no) = ?', [$identity]);
                }

                if ($this->columnExists($table, 'biometric_employee_id')) {
                    $q->orWhereRaw('TRIM(biometric_employee_id) = ?', [$identity]);
                }

                if ($this->columnExists($table, 'crosschex_id')) {
                    $q->orWhereRaw('TRIM(crosschex_id) = ?', [$identity]);
                }
            }
        });
    }

    protected function personIdentityValues(array $person): array
    {
        return collect([
            $person['employee_no'] ?? null,
            $person['biometric_employee_id'] ?? null,
            $person['crosschex_id'] ?? null,
        ])
            ->map(fn ($value) => $this->cleanString($value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function makePersonKey(array $person): string
    {
        $identities = $this->personIdentityValues($person);

        return 'PERSON:'.($identities[0] ?? md5(json_encode($person)));
    }

    protected function asManilaDate(string|Carbon $date): Carbon
    {
        return $date instanceof Carbon
            ? $date->copy()->timezone('Asia/Manila')->startOfDay()
            : Carbon::parse($date, 'Asia/Manila')->startOfDay();
    }

    protected function cleanString($value): string
    {
        return trim((string) ($value ?? ''));
    }

    protected function biometricTimeColumn(): string
    {
        $table = (new MirasolBiometricsLog)->getTable();

        foreach (['check_time', 'date_time', 'datetime', 'punch_time', 'scan_time', 'log_time'] as $column) {
            if ($this->columnExists($table, $column)) {
                return $column;
            }
        }

        return 'check_time';
    }

    protected function columnExists(string $table, string $column): bool
    {
        $key = $table.'.'.$column;

        if (array_key_exists($key, $this->columnCache)) {
            return $this->columnCache[$key];
        }

        try {
            $this->columnCache[$key] = Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            $this->columnCache[$key] = false;
        }

        return $this->columnCache[$key];
    }
}
