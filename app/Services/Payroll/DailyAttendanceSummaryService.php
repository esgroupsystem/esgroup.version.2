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

    private const FULL_DAY_PAYABLE_HOURS = 8.00;

    private const HALF_DAY_PAYABLE_HOURS = 4.00;

    private const DEFAULT_GRACE_MINUTES = 15;

    /*
     * Duplicate punch rule:
     * If the first punch and last punch are within 30 minutes,
     * treat the later punch as duplicate scan, not a valid timeout.
     */
    private const DUPLICATE_PUNCH_WINDOW_MINUTES = 30;

    private const REGULAR_HOLIDAY_WORKED_PAY_DAYS = 2.00;

    private const SPECIAL_HOLIDAY_WORKED_PAY_DAYS = 1.30;

    private array $columnCache = [];

    private ?string $biometricTimeColumnCache = null;

    public function buildForDate(string|Carbon $date): void
    {
        $workDate = $this->asManilaDate($date);

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
            @set_time_limit(300);

            $this->buildForDate($current);

            $current->addDay();
        }
    }

    protected function collectPeopleForDate(Carbon $workDate): Collection
    {
        $people = collect();

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

        PayrollAttendanceAdjustment::query()
            ->whereDate('work_date', $workDate->toDateString())
            ->where('adjustment_type', '!=', PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER)
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

        $globalDisasterAdjustment = $this->globalDisasterAdjustmentForDate($workDate);

        if ($globalDisasterAdjustment && $logs->isNotEmpty()) {
            $adjustment = $globalDisasterAdjustment;
        }

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

    protected function globalDisasterAdjustmentForDate(Carbon $workDate): ?PayrollAttendanceAdjustment
    {
        return PayrollAttendanceAdjustment::query()
            ->whereDate('work_date', $workDate->toDateString())
            ->where('adjustment_type', PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER)
            ->latest('id')
            ->first();
    }

    protected function isTyphoonDisasterAdjustment(?PayrollAttendanceAdjustment $adjustment): bool
    {
        return $adjustment?->adjustment_type === PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER;
    }

    protected function resolveScheduleForPersonDate(array $person, Carbon $workDate): ?EmployeePlottingSchedule
    {
        $table = (new EmployeePlottingSchedule)->getTable();

        $baseQuery = EmployeePlottingSchedule::query()
            ->where(function ($query) use ($person) {
                $this->applySchedulePersonMatch($query, $person);
            });

        if ($this->columnExists($table, 'work_date')) {
            $exactDateSchedule = (clone $baseQuery)
                ->whereDate('work_date', $workDate->toDateString())
                ->latest('updated_at')
                ->latest('id')
                ->first();

            if ($exactDateSchedule) {
                return $exactDateSchedule;
            }

            $permanentSchedule = (clone $baseQuery)
                ->whereNull('work_date')
                ->latest('updated_at')
                ->latest('id')
                ->first();

            if ($permanentSchedule) {
                return $permanentSchedule;
            }
        }

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
        $remarks = [];

        $rawLogCount = $logs->count();
        $hasRawBiometrics = $rawLogCount > 0;
        $biometricTimeColumn = $this->biometricTimeColumn();

        [$actualTimeIn, $actualTimeOut] = $this->resolveBiometricActualInOut(
            $logs,
            $biometricTimeColumn,
            $remarks
        );

        $shiftName = $schedule?->shift_name ?: null;
        $scheduleStatus = $this->cleanString($schedule?->status) ?: 'scheduled';
        $scheduleRemarks = $schedule?->remarks;
        $dayOff = $schedule?->day_off;
        $graceMinutes = $this->resolveGraceMinutes($schedule);

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

        $isTyphoonDisasterAdjustment = $this->isTyphoonDisasterAdjustment($adjustment);
        $ignoreLate = (bool) ($adjustment?->ignore_late ?? false) || $isTyphoonDisasterAdjustment;
        $ignoreUndertime = (bool) ($adjustment?->ignore_undertime ?? false) || $isTyphoonDisasterAdjustment;

        $lateMinutes = 0;
        $undertimeMinutes = 0;
        $overtimeMinutes = 0;
        $workedMinutes = 0;
        $payableDays = 0.00;
        $payableHours = 0.00;
        $attendanceStatus = 'absent';

        if (! $schedule) {
            $remarks[] = 'No plotted permanent schedule found. Please check Permanent Plotting Schedule.';
        } elseif ($this->isLegacyDateBasedSchedule($schedule)) {
            $remarks[] = 'Legacy plotted schedule row used as permanent fallback. Re-save employee in Permanent Plotting Schedule.';
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

        if ($graceMinutes > 0) {
            $remarks[] = 'Late grace period applied: '.$graceMinutes.' minute(s).';
        }

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
                if ($this->scheduleIsOvernight($scheduledTimeIn, $scheduledTimeOut)) {
                    $actualTimeOut->addDay();
                } else {
                    $actualTimeOut = null;
                    $remarks[] = 'Adjusted time out is not later than time in. Treated as no valid time out.';
                }
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
            $workedMinutes = (int) $actualTimeIn->diffInMinutes($actualTimeOut);
        }

        $isAutomaticHalfDay = $this->isAutomaticHalfDay($actualTimeIn, $actualTimeOut);

        if ($isTyphoonDisasterAdjustment && $hasRawBiometrics) {
            $attendanceStatus = 'adjusted_present';
            $workedMinutes = max($workedMinutes, self::FULL_DAY_MINUTES);
            $lateMinutes = 0;
            $undertimeMinutes = 0;
            $payableDays = self::FULL_DAY_PAYABLE_DAYS;
            $payableHours = self::FULL_DAY_PAYABLE_HOURS;

            $remarks[] = 'Typhoon / Disaster adjustment applied. Employee has time-in on this date, so the whole day is paid and late/undertime are ignored.';
        } elseif ($isHoliday) {
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
                $undertimeMinutes = self::FULL_DAY_MINUTES - self::HALF_DAY_MINUTES;
                $payableDays = self::HALF_DAY_PAYABLE_DAYS;
                $payableHours = self::HALF_DAY_PAYABLE_HOURS;

                $remarks[] = 'No valid time out. Half day paid based on company policy.';
            } elseif ($holidayQualified || $adjustmentIsPaid) {
                $attendanceStatus = 'holiday';
                $payableDays = self::FULL_DAY_PAYABLE_DAYS;
                $payableHours = self::FULL_DAY_PAYABLE_HOURS;

                $remarks[] = 'Paid holiday. Previous day qualified. After-holiday record is not required.';
            } else {
                $attendanceStatus = 'holiday_unpaid';
                $payableDays = 0.00;
                $payableHours = 0.00;

                $remarks[] = 'Holiday unpaid. Previous day did not qualify.';
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
                $attendanceStatus = 'rest_day';
                $remarks[] = 'Rest day has no valid time out. Base rest day pay retained.';
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
            $lateMinutes = 0;
            $undertimeMinutes = self::FULL_DAY_MINUTES - self::HALF_DAY_MINUTES;
            $payableDays = self::HALF_DAY_PAYABLE_DAYS;
            $payableHours = self::HALF_DAY_PAYABLE_HOURS;

            $remarks[] = 'No valid time out. Half day paid based on company policy.';
        } elseif ($isFlexible) {
            if ($workedMinutes >= self::FULL_DAY_MINUTES) {
                $attendanceStatus = 'present';
                $payableDays = self::FULL_DAY_PAYABLE_DAYS;
                $payableHours = self::FULL_DAY_PAYABLE_HOURS;

                $remarks[] = 'Flexible shift completed 9 clock hours / 8 paid hours.';
            } else {
                $attendanceStatus = 'undertime';
                $undertimeMinutes = max(0, self::FULL_DAY_MINUTES - $workedMinutes);

                [$payableDays, $payableHours] = $this->payUnitsFromMinutes($workedMinutes);

                $remarks[] = 'Flexible shift below 9 clock hours. Payable hours converted using 8 paid hours per day.';
            }
        } else {
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

            if ($attendanceStatus === 'present') {
                $payableDays = self::FULL_DAY_PAYABLE_DAYS;
                $payableHours = self::FULL_DAY_PAYABLE_HOURS;
            } else {
                $deductionMinutes = max(0, (int) $lateMinutes + (int) $undertimeMinutes);
                $payableMinutes = max(0, self::FULL_DAY_MINUTES - $deductionMinutes);

                [$payableDays, $payableHours] = $this->payUnitsFromMinutes($payableMinutes);

                $remarks[] = 'Late/undertime deducted from 8 paid hours while schedule remains 9 clock hours including lunch.';
            }
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

    protected function resolveBiometricActualInOut(
        Collection $logs,
        string $timeColumn,
        array &$remarks
    ): array {
        if ($logs->isEmpty()) {
            return [null, null];
        }

        $firstLog = $logs->first();

        if (empty($firstLog->{$timeColumn})) {
            return [null, null];
        }

        $actualTimeIn = Carbon::parse($firstLog->{$timeColumn}, 'Asia/Manila');

        if ($logs->count() < 2) {
            return [$actualTimeIn, null];
        }

        $lastLog = $logs->last();

        if (empty($lastLog->{$timeColumn})) {
            return [$actualTimeIn, null];
        }

        $candidateTimeOut = Carbon::parse($lastLog->{$timeColumn}, 'Asia/Manila');

        if ($candidateTimeOut->lessThanOrEqualTo($actualTimeIn)) {
            $remarks[] = 'Duplicate or invalid biometric timeout ignored.';

            return [$actualTimeIn, null];
        }

        $minutesBetweenFirstAndLast = (int) $actualTimeIn->diffInMinutes($candidateTimeOut);

        if ($minutesBetweenFirstAndLast <= self::DUPLICATE_PUNCH_WINDOW_MINUTES) {
            $remarks[] = 'Biometric punches within 30 minutes from first time in were treated as duplicate scans, not time out.';

            return [$actualTimeIn, null];
        }

        return [$actualTimeIn, $candidateTimeOut];
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

    protected function resolveGraceMinutes(?EmployeePlottingSchedule $schedule): int
    {
        $configuredDefault = (int) config('payroll.attendance.late_grace_minutes', self::DEFAULT_GRACE_MINUTES);

        /*
         | Company rule:
         | First 15 minutes after scheduled time-in is free.
         | If schedule grace_minutes is blank, null, or 0, use the company default.
         | This keeps Attendance Summary and Payroll using the same effective late minutes.
         */
        $scheduleGrace = $schedule?->grace_minutes;

        if (is_numeric($scheduleGrace) && (int) $scheduleGrace > 0) {
            return max(0, (int) $scheduleGrace);
        }

        return max(0, $configuredDefault);
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

        $scheduledIn = Carbon::parse(
            $workDate->toDateString().' '.$scheduledTimeIn,
            'Asia/Manila'
        );

        $scheduledOut = Carbon::parse(
            $workDate->toDateString().' '.$scheduledTimeOut,
            'Asia/Manila'
        );

        if ($scheduledOut->lessThanOrEqualTo($scheduledIn)) {
            $scheduledOut->addDay();
        }

        if ($actualTimeOut->lessThanOrEqualTo($actualTimeIn)) {
            $actualTimeOut->addDay();
        }

        $rawLateMinutes = (int) $scheduledIn->diffInMinutes($actualTimeIn, false);

        $lateMinutes = $this->roundedLateDeductionMinutes(
            $rawLateMinutes,
            $graceMinutes
        );

        if ($actualTimeOut->lt($scheduledOut)) {
            $rawUndertimeMinutes = (int) ceil($actualTimeOut->floatDiffInMinutes($scheduledOut));

            $undertimeMinutes = $this->roundedUndertimeDeductionMinutes($rawUndertimeMinutes);
        }

        return [$lateMinutes, $undertimeMinutes];
    }

    protected function roundedUndertimeDeductionMinutes(int $rawUndertimeMinutes): int
    {
        if ($rawUndertimeMinutes <= 0) {
            return 0;
        }

        $graceMinutes = max(
            0,
            (int) config('payroll.attendance.undertime_grace_minutes', 5)
        );

        if ($rawUndertimeMinutes <= $graceMinutes) {
            return 0;
        }

        $blockMinutes = max(
            1,
            (int) config('payroll.attendance.undertime_deduction_block_minutes', 30)
        );

        return (int) (ceil($rawUndertimeMinutes / $blockMinutes) * $blockMinutes);
    }

    protected function roundedLateDeductionMinutes(int $rawLateMinutes, int $graceMinutes): int
    {
        if ($rawLateMinutes <= $graceMinutes) {
            return 0;
        }

        $blockMinutes = max(1, (int) config('payroll.attendance.late_deduction_block_minutes', 30));

        return (int) (ceil($rawLateMinutes / $blockMinutes) * $blockMinutes);
    }

    protected function payUnitsFromMinutes(int $minutes): array
    {
        $payableClockMinutes = max(0, min(self::FULL_DAY_MINUTES, $minutes));
        $deductedMinutes = max(0, self::FULL_DAY_MINUTES - $payableClockMinutes);
        $paidMinutes = max(0, ((int) config('payroll.attendance.paid_minutes_per_day', 480)) - $deductedMinutes);
        $paidHours = round($paidMinutes / 60, 2);

        return [
            round($paidHours / self::FULL_DAY_PAYABLE_HOURS, 2),
            $paidHours,
        ];
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
        /*
         | Company holiday rule:
         | Only the day BEFORE the holiday is checked.
         | The day AFTER the holiday is not required.
         */
        if ($this->adjustmentQualifiesForPay($holidayAdjustment)) {
            return true;
        }

        if (! config('payroll.holiday_requires_before_work_only', true)) {
            return true;
        }

        return $this->isHolidayPreviousDateQualified(
            $person,
            $holidayDate->copy()->subDay()
        );
    }

    protected function isHolidayPreviousDateQualified(array $person, Carbon $date): bool
    {
        $schedule = $this->resolveScheduleForPersonDate($person, $date);
        $holiday = $this->resolveHoliday($date);

        /*
         | Holiday before holiday:
         | Work -> Holiday -> Holiday = paid.
         */
        if ($holiday) {
            return true;
        }

        /*
         | Day off before holiday:
         | Dayoff -> Holiday = paid.
         */
        if ($schedule && $this->scheduleIndicatesRestDay($schedule->status, $schedule->day_off, $date)) {
            return true;
        }

        /*
         | Leave before holiday:
         | Leave -> Holiday = paid.
         */
        if ($schedule && $this->scheduleIndicatesLeave($schedule->status)) {
            return true;
        }

        $adjustment = PayrollAttendanceAdjustment::query()
            ->whereDate('work_date', $date->toDateString())
            ->where(function ($query) use ($person): void {
                $this->applyAdjustmentPersonMatch($query, $person);
            })
            ->latest('id')
            ->first();

        /*
         | Paid adjustment / official business / adjusted present before holiday.
         */
        if ($this->adjustmentQualifiesForPay($adjustment)) {
            return true;
        }

        /*
         | Work before holiday:
         | Any biometric record/time-in qualifies.
         | No need to require the day after holiday.
         */
        $logs = $this->logsForPersonDate($person, $date, $schedule);

        if ($logs->isNotEmpty()) {
            return true;
        }

        return false;
    }

    protected function adjustmentQualifiesForPay(?PayrollAttendanceAdjustment $adjustment): bool
    {
        if (! $adjustment) {
            return false;
        }

        if ((bool) ($adjustment->is_paid ?? false)) {
            return true;
        }

        if ($this->isTyphoonDisasterAdjustment($adjustment)) {
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
            'typhoon',
            'disaster',
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
                1.00,
                'Special holiday work detected. Payroll will add only +30% premium after approved adjustment validation.',
            ];
        }

        return [
            1.00,
            'Regular holiday work detected. Payroll will add only +100% premium after approved adjustment validation.',
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

    protected function isLegacyDateBasedSchedule(EmployeePlottingSchedule $schedule): bool
    {
        $table = (new EmployeePlottingSchedule)->getTable();

        return $this->columnExists($table, 'work_date') && ! empty($schedule->work_date);
    }

    protected function scheduleIsOvernight(?string $scheduledTimeIn, ?string $scheduledTimeOut): bool
    {
        if (! $scheduledTimeIn || ! $scheduledTimeOut) {
            return false;
        }

        return Carbon::parse($scheduledTimeOut)->lessThanOrEqualTo(Carbon::parse($scheduledTimeIn));
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
        if ($this->biometricTimeColumnCache !== null) {
            return $this->biometricTimeColumnCache;
        }

        $table = (new MirasolBiometricsLog)->getTable();

        foreach (['check_time', 'date_time', 'datetime', 'punch_time', 'scan_time', 'log_time'] as $column) {
            if ($this->columnExists($table, $column)) {
                $this->biometricTimeColumnCache = $column;

                return $column;
            }
        }

        $this->biometricTimeColumnCache = 'check_time';

        return $this->biometricTimeColumnCache;
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
