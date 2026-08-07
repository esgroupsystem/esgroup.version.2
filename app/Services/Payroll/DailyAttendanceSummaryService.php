<?php

namespace App\Services\Payroll;

use App\Models\DailyAttendanceSummary;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\Holiday;
use App\Models\MirasolBiometricsLog;
use App\Models\PayrollAttendanceAdjustment;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DailyAttendanceSummaryService
{
    private const SCHEDULED_CLOCK_MINUTES = 540; // 9 clock hours, including lunch

    private const FULL_DAY_PAID_MINUTES = 480; // 8 paid working hours

    private const HALF_DAY_PAID_MINUTES = 240; // 4 paid working hours

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

    public function __construct(
        private readonly EmployeeBiometricIdentityService $identityService
    ) {}

    public function buildForDate(string|Carbon $date): void
    {
        $workDate = $this->asManilaDate($date);

        /*
         * Rebuilding a date is atomic. If one employee fails, the previous
         * complete summary for that date remains instead of leaving HR with a
         * partially rebuilt roster.
         */
        DB::transaction(function () use ($workDate): void {
            $people = $this->collectPeopleForDate($workDate);

            DailyAttendanceSummary::query()
                ->whereDate('work_date', $workDate->toDateString())
                ->delete();

            foreach ($people as $person) {
                $this->buildForPersonDate($person, $workDate);
            }
        }, 3);
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

        $this->assertPeriodRosterCoverage($start, $end);
    }

    protected function assertPeriodRosterCoverage(Carbon $startDate, Carbon $endDate): void
    {
        $summaryTable = (new DailyAttendanceSummary)->getTable();

        if (! $this->columnExists($summaryTable, 'employee_biometric_id')) {
            return;
        }

        $eligibleIds = EmployeeBiometric::query()
            ->payrollActive()
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values();

        $expectedDays = (int) $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1;
        $expectedRows = $eligibleIds->count() * $expectedDays;

        $actualRows = DailyAttendanceSummary::query()
            ->whereBetween('work_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->whereIn('employee_biometric_id', $eligibleIds)
            ->count();

        if ($actualRows !== $expectedRows) {
            throw new \RuntimeException(sprintf(
                'Attendance Summary roster coverage check failed. Expected %d row(s) for %d payroll-eligible employee(s) across %d day(s), but found %d row(s).',
                $expectedRows,
                $eligibleIds->count(),
                $expectedDays,
                $actualRows
            ));
        }
    }

    protected function collectPeopleForDate(Carbon $workDate): Collection
    {
        $people = collect();

        EmployeeBiometric::query()
            ->payrollActive()
            ->get()
            ->each(function (EmployeeBiometric $employee) use ($people): void {
                $snapshot = $this->identityService->snapshot($employee);

                $this->putPerson($people, [
                    'employee_biometric_id' => $employee->id,
                    'crosschex_id' => $snapshot['crosschex_id'],
                    'biometric_employee_id' => $snapshot['biometric_employee_id'],
                    'employee_no' => $snapshot['employee_no'],
                    'employee_name' => $snapshot['employee_name'],
                    'source_employee_id' => $this->cleanString($employee->source_employee_id),
                    'source_employee_no' => $this->cleanString($employee->source_employee_no),
                    'source_crosschex_id' => $this->cleanString($employee->source_crosschex_id),
                    'source_key' => $this->cleanString($employee->source_key),
                    'source_crosschex_account' => $this->cleanString($employee->source_crosschex_account),
                ]);
            });

        EmployeePlottingSchedule::query()
            ->whereNotNull('employee_biometric_id')
            ->get()
            ->each(function (EmployeePlottingSchedule $row) use ($people): void {
                $employee = $this->identityService->resolveFromModel($row, true);

                if (! $employee) {
                    return;
                }

                $snapshot = $this->identityService->snapshot($employee);

                $this->putPerson($people, [
                    'employee_biometric_id' => $employee->id,
                    'crosschex_id' => $snapshot['crosschex_id'],
                    'biometric_employee_id' => $snapshot['biometric_employee_id'],
                    'employee_no' => $snapshot['employee_no'],
                    'employee_name' => $snapshot['employee_name'],
                    'source_employee_id' => $this->cleanString($employee->source_employee_id),
                    'source_employee_no' => $this->cleanString($employee->source_employee_no),
                    'source_crosschex_id' => $this->cleanString($employee->source_crosschex_id),
                    'source_key' => $this->cleanString($employee->source_key),
                    'source_crosschex_account' => $this->cleanString($employee->source_crosschex_account),
                ]);
            });

        MirasolBiometricsLog::query()
            ->whereDate($this->biometricTimeColumn(), $workDate->toDateString())
            ->get()
            ->each(function (MirasolBiometricsLog $row) use ($people): void {
                $employee = $this->identityService->resolve(
                    biometricEmployeeId: $this->cleanString($row->employee_id ?? null),
                    employeeNo: $this->cleanString($row->employee_no ?? null),
                    employeeName: $this->cleanString($row->employee_name ?? null),
                    crosschexId: $this->cleanString($row->crosschex_id ?? null),
                    onlyPayrollActive: true,
                    crosschexAccount: $this->cleanString($row->crosschex_account ?? null)
                );

                if (! $employee) {
                    return;
                }

                $snapshot = $this->identityService->snapshot($employee);

                $this->putPerson($people, [
                    'employee_biometric_id' => $employee->id,
                    'crosschex_id' => $snapshot['crosschex_id'],
                    'biometric_employee_id' => $snapshot['biometric_employee_id'],
                    'employee_no' => $snapshot['employee_no'],
                    'employee_name' => $snapshot['employee_name'],
                    'source_employee_id' => $this->cleanString($employee->source_employee_id),
                    'source_employee_no' => $this->cleanString($employee->source_employee_no),
                    'source_crosschex_id' => $this->cleanString($employee->source_crosschex_id),
                    'source_key' => $this->cleanString($employee->source_key),
                    'source_crosschex_account' => $this->cleanString($employee->source_crosschex_account),
                ]);
            });

        PayrollAttendanceAdjustment::query()
            ->whereDate('work_date', $workDate->toDateString())
            ->where('adjustment_type', '!=', PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER)
            ->get()
            ->each(function (PayrollAttendanceAdjustment $row) use ($people): void {
                $employee = $this->identityService->resolveFromModel($row, true);

                if (! $employee) {
                    return;
                }

                $snapshot = $this->identityService->snapshot($employee);

                $this->putPerson($people, [
                    'employee_biometric_id' => $employee->id,
                    'crosschex_id' => $snapshot['crosschex_id'],
                    'biometric_employee_id' => $snapshot['biometric_employee_id'],
                    'employee_no' => $snapshot['employee_no'],
                    'employee_name' => $snapshot['employee_name'],
                    'source_employee_id' => $this->cleanString($employee->source_employee_id),
                    'source_employee_no' => $this->cleanString($employee->source_employee_no),
                    'source_crosschex_id' => $this->cleanString($employee->source_crosschex_id),
                    'source_key' => $this->cleanString($employee->source_key),
                    'source_crosschex_account' => $this->cleanString($employee->source_crosschex_account),
                ]);
            });

        return $people->values();
    }

    protected function putPerson(Collection $people, array $newPerson): void
    {
        $newCanonicalId = ! empty($newPerson['employee_biometric_id'])
            ? (int) $newPerson['employee_biometric_id']
            : null;

        /*
         * Canonical EmployeeBiometric IDs are authoritative.
         *
         * Previous behavior merged people whenever ANY legacy identifier
         * overlapped. CrossChex accounts can legitimately reuse values such as
         * employee_id "1", so two different active employees could collapse
         * into one Attendance Summary person. Once that happened, the missing
         * employee also disappeared from payroll because payroll used summary
         * rows as its roster source.
         *
         * Never merge two different non-null canonical IDs.
         */
        if ($newCanonicalId !== null) {
            $canonicalKey = 'EMPLOYEE_BIOMETRIC:'.$newCanonicalId;

            if (! $people->has($canonicalKey)) {
                $people->put($canonicalKey, $newPerson);

                return;
            }

            $people->put(
                $canonicalKey,
                $this->mergePersonPayloads($people->get($canonicalKey), $newPerson)
            );

            return;
        }

        $newIdentities = $this->personIdentityValues($newPerson);

        if (empty($newIdentities)) {
            return;
        }

        $existingKey = null;

        foreach ($people as $key => $existingPerson) {
            /*
             * A legacy-only person must not absorb an already resolved
             * canonical employee through a weak reused identifier.
             */
            if (! empty($existingPerson['employee_biometric_id'])) {
                continue;
            }

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

        $people->put(
            $existingKey,
            $this->mergePersonPayloads($people->get($existingKey), $newPerson)
        );
    }

    protected function mergePersonPayloads(array $existingPerson, array $newPerson): array
    {
        return [
            'employee_biometric_id' => $existingPerson['employee_biometric_id'] ?: ($newPerson['employee_biometric_id'] ?? null),
            'crosschex_id' => $existingPerson['crosschex_id'] ?: ($newPerson['crosschex_id'] ?? ''),
            'biometric_employee_id' => $existingPerson['biometric_employee_id'] ?: ($newPerson['biometric_employee_id'] ?? ''),
            'employee_no' => $existingPerson['employee_no'] ?: ($newPerson['employee_no'] ?? ''),
            'employee_name' => $existingPerson['employee_name'] ?: ($newPerson['employee_name'] ?? ''),
            'source_employee_id' => $existingPerson['source_employee_id'] ?: ($newPerson['source_employee_id'] ?? ''),
            'source_employee_no' => $existingPerson['source_employee_no'] ?: ($newPerson['source_employee_no'] ?? ''),
            'source_crosschex_id' => $existingPerson['source_crosschex_id'] ?: ($newPerson['source_crosschex_id'] ?? ''),
            'source_key' => $existingPerson['source_key'] ?: ($newPerson['source_key'] ?? ''),
            'source_crosschex_account' => $existingPerson['source_crosschex_account'] ?: ($newPerson['source_crosschex_account'] ?? ''),
        ];
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
        $scheduleStatus = $schedule
            ? ($this->cleanString($schedule->status) ?: 'scheduled')
            : 'no_schedule';
        $scheduleRemarks = $schedule?->remarks;
        $dayOff = $schedule?->resolvedDayOffs() ?? $schedule?->day_off;
        $graceMinutes = $this->resolveGraceMinutes($schedule);

        $workdayRules = $this->resolveWorkdayRules($schedule);
        $workdayType = $workdayRules['workday_type'];
        $paidMinutesPerDay = $workdayRules['paid_minutes_per_day'];
        $lunchBreakMinutes = $workdayRules['lunch_break_minutes'];
        $scheduledClockMinutes = $workdayRules['scheduled_clock_minutes'];
        $fullDayPayableHours = round($paidMinutesPerDay / 60, 2);
        $halfDayPaidMinutes = (int) round($paidMinutesPerDay / 2);
        $halfDayPayableHours = round($halfDayPaidMinutes / 60, 2);

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
        $clockWorkedMinutes = 0;
        $workedMinutes = 0;
        $unpaidBreakMinutes = 0;
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
            $clockWorkedMinutes = (int) $actualTimeIn->diffInMinutes($actualTimeOut);
            $unpaidBreakMinutes = $this->unpaidBreakOverlapMinutes(
                $workDate,
                $actualTimeIn,
                $actualTimeOut,
                $lunchBreakMinutes
            );
            $workedMinutes = max(0, $clockWorkedMinutes - $unpaidBreakMinutes);

            if ($unpaidBreakMinutes > 0) {
                $remarks[] = 'Unpaid lunch deducted from worked time: '.$unpaidBreakMinutes.' minute(s).';
            }
        }

        $isAutomaticHalfDay = $this->isAutomaticHalfDay($actualTimeIn, $actualTimeOut);

        if ($isTyphoonDisasterAdjustment && $hasRawBiometrics) {
            $attendanceStatus = 'adjusted_present';
            $clockWorkedMinutes = max($clockWorkedMinutes, $scheduledClockMinutes);
            $workedMinutes = max($workedMinutes, $paidMinutesPerDay);
            $lateMinutes = 0;
            $undertimeMinutes = 0;
            $payableDays = self::FULL_DAY_PAYABLE_DAYS;
            $payableHours = $fullDayPayableHours;

            $remarks[] = 'Typhoon / Disaster adjustment applied. Employee has time-in on this date, so the whole day is paid and late/undertime are ignored.';
        } elseif ($isHoliday) {
            [$holidayWorkedPayDays, $holidayRateLabel] = $this->holidayWorkedPayDays($holidayType);
            $holidayQualified = $this->isHolidayPayQualified($person, $workDate, $adjustment);

            if ($hasValidInOut) {
                $attendanceStatus = 'holiday_worked';
                $payableDays = $holidayWorkedPayDays;
                $payableHours = round($fullDayPayableHours * $holidayWorkedPayDays, 2);

                $remarks[] = 'Holiday worked rate applied: '.$holidayRateLabel.'.';
            } elseif ($isAutomaticHalfDay) {
                $attendanceStatus = 'half_day';
                $clockWorkedMinutes = $halfDayPaidMinutes;
                $workedMinutes = $halfDayPaidMinutes;
                $undertimeMinutes = $paidMinutesPerDay - $halfDayPaidMinutes;
                $payableDays = self::HALF_DAY_PAYABLE_DAYS;
                $payableHours = $halfDayPayableHours;

                $remarks[] = 'No valid time out. Half day paid based on company policy.';
            } elseif ($holidayQualified || $adjustmentIsPaid) {
                $attendanceStatus = 'holiday';
                $payableDays = self::FULL_DAY_PAYABLE_DAYS;
                $payableHours = $fullDayPayableHours;

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
            $payableHours = $fullDayPayableHours;

            $remarks[] = 'Paid leave/day type applied.';
        } elseif ($isRestDay) {
            $payableDays = self::FULL_DAY_PAYABLE_DAYS;
            $payableHours = $fullDayPayableHours;

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
            $clockWorkedMinutes = $halfDayPaidMinutes;
            $workedMinutes = $halfDayPaidMinutes;
            $lateMinutes = 0;
            $undertimeMinutes = $paidMinutesPerDay - $halfDayPaidMinutes;
            $payableDays = self::HALF_DAY_PAYABLE_DAYS;
            $payableHours = $halfDayPayableHours;

            $remarks[] = 'No valid time out. Half day paid based on company policy.';
        } elseif ($isFlexible) {
            if ($clockWorkedMinutes >= $scheduledClockMinutes) {
                $attendanceStatus = 'present';
                $payableDays = self::FULL_DAY_PAYABLE_DAYS;
                $payableHours = $fullDayPayableHours;

                $remarks[] = 'Flexible shift completed '.round($scheduledClockMinutes / 60, 2).' clock hours / '.$fullDayPayableHours.' paid hours.';
            } else {
                $attendanceStatus = 'undertime';
                $rawUndertimeMinutes = max(0, $scheduledClockMinutes - $clockWorkedMinutes);
                $undertimeMinutes = $this->roundedUndertimeDeductionMinutes($rawUndertimeMinutes);

                [$payableDays, $payableHours] = $this->payUnitsAfterDeductions($undertimeMinutes, $paidMinutesPerDay);

                $remarks[] = 'Flexible shift below '.round($scheduledClockMinutes / 60, 2).' clock hours. Payable hours converted using '.$fullDayPayableHours.' paid hours per day.';
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
                $payableHours = $fullDayPayableHours;
            } else {
                $deductionMinutes = max(0, (int) $lateMinutes + (int) $undertimeMinutes);

                [$payableDays, $payableHours] = $this->payUnitsAfterDeductions($deductionMinutes, $paidMinutesPerDay);

                $remarks[] = 'Late/undertime deducted from '.$fullDayPayableHours.' paid hours while the schedule contains '.round($scheduledClockMinutes / 60, 2).' clock hours including lunch.';
            }
        }

        if (
            $hasAdjustment
            && $adjustmentIsPaid
            && in_array($attendanceStatus, ['absent', 'incomplete_log', 'no_schedule', 'holiday_unpaid'], true)
        ) {
            $attendanceStatus = 'adjusted_present';
            $payableDays = self::FULL_DAY_PAYABLE_DAYS;
            $payableHours = $fullDayPayableHours;

            $remarks[] = 'Paid adjustment forced payable attendance.';
        }

        if ($clockWorkedMinutes > $scheduledClockMinutes) {
            $overtimeMinutes = $clockWorkedMinutes - $scheduledClockMinutes;
        }

        $employeeBiometricId = ! empty($person['employee_biometric_id'])
            ? (int) $person['employee_biometric_id']
            : null;
        $employeeNo = $this->cleanString($person['employee_no'] ?? null);
        $biometricEmployeeId = $this->cleanString($person['biometric_employee_id'] ?? null);

        $summaryKeys = [
            'work_date' => $workDate->toDateString(),
        ];

        if ($employeeBiometricId !== null && $this->columnExists((new DailyAttendanceSummary)->getTable(), 'employee_biometric_id')) {
            $summaryKeys['employee_biometric_id'] = $employeeBiometricId;
        } elseif ($employeeNo !== '') {
            $summaryKeys['employee_no'] = $employeeNo;
        } else {
            $summaryKeys['biometric_employee_id'] = $biometricEmployeeId;
        }

        return DailyAttendanceSummary::updateOrCreate(
            $summaryKeys,
            [
                'employee_biometric_id' => $employeeBiometricId,
                'biometric_employee_id' => $biometricEmployeeId,
                'employee_no' => $employeeNo,
                'employee_name' => $this->cleanString($person['employee_name'] ?? null),

                'plotting_schedule_id' => $schedule?->id,
                'attendance_adjustment_id' => $adjustment?->id,
                'holiday_id' => data_get($holiday, 'id'),
                'crosschex_id' => $this->cleanString($person['crosschex_id'] ?? $schedule?->crosschex_id),

                'shift_name' => $shiftName ?: 'No Schedule',
                'schedule_status' => $scheduleStatus,
                'scheduled_time_in' => $scheduledTimeIn,
                'scheduled_time_out' => $scheduledTimeOut,
                'grace_minutes' => $graceMinutes,
                'schedule_remarks' => $scheduleRemarks,

                'actual_time_in' => $actualTimeIn,
                'actual_time_out' => $actualTimeOut,
                'raw_log_count' => $rawLogCount,
                'has_biometrics' => $hasRawBiometrics,

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
                'adjusted_time_in' => $adjustment?->adjusted_time_in,
                'adjusted_time_out' => $adjustment?->adjusted_time_out,
                'adjusted_day_type' => $adjustment?->adjusted_day_type,
                'adjustment_is_paid' => $adjustmentIsPaid,
                'ignore_late' => $ignoreLate,
                'ignore_undertime' => $ignoreUndertime,
                'adjustment_reason' => $adjustmentReason,
                'adjustment_remarks' => $adjustmentRemarks,

                'is_absent' => $attendanceStatus === 'absent',
                'is_incomplete_log' => $attendanceStatus === 'incomplete_log',

                'payable_days' => round((float) $payableDays, 2),
                'payable_hours' => round((float) $payableHours, 2),

                'remarks' => implode(' ', array_filter($remarks)),
                'computed_at' => now('Asia/Manila'),
                'meta' => [
                    'schedule_mode' => $isFlexible ? 'flexible' : ($schedule ? 'regular' : 'none'),
                    'has_configured_schedule' => $schedule !== null,
                    'workday_type' => $workdayType,
                    'paid_work_hours' => $fullDayPayableHours,
                    'clock_worked_minutes' => max(0, (int) $clockWorkedMinutes),
                    'unpaid_break_minutes' => max(0, (int) $unpaidBreakMinutes),
                    'paid_worked_minutes' => max(0, (int) $workedMinutes),
                    'scheduled_clock_minutes' => $scheduledClockMinutes,
                    'paid_minutes_per_day' => $paidMinutesPerDay,
                ],
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

    protected function payUnitsAfterDeductions(
        int $deductionMinutes,
        ?int $paidMinutesPerDay = null
    ): array {
        $paidMinutesPerDay = max(
            1,
            $paidMinutesPerDay
                ?? (int) config('payroll.attendance.paid_minutes_per_day', self::FULL_DAY_PAID_MINUTES)
        );

        $deductionMinutes = max(0, min($paidMinutesPerDay, $deductionMinutes));
        $paidMinutes = max(0, $paidMinutesPerDay - $deductionMinutes);
        $paidHours = round($paidMinutes / 60, 2);

        return [
            round($paidMinutes / $paidMinutesPerDay, 2),
            $paidHours,
        ];
    }

    protected function unpaidBreakOverlapMinutes(
        Carbon $workDate,
        Carbon $actualTimeIn,
        Carbon $actualTimeOut,
        ?int $configuredBreakMinutes = null
    ): int {
        $configuredBreakMinutes = max(
            0,
            $configuredBreakMinutes
                ?? (int) config(
                    'payroll.attendance.unpaid_break_minutes',
                    self::SCHEDULED_CLOCK_MINUTES - self::FULL_DAY_PAID_MINUTES
                )
        );

        if ($configuredBreakMinutes === 0 || $actualTimeOut->lessThanOrEqualTo($actualTimeIn)) {
            return 0;
        }

        $breakStartValue = trim((string) config('payroll.attendance.unpaid_break_start', '12:00'));
        $breakEndValue = trim((string) config('payroll.attendance.unpaid_break_end', '13:00'));

        if ($breakStartValue === '' || $breakEndValue === '') {
            return min($configuredBreakMinutes, max(0, (int) $actualTimeIn->diffInMinutes($actualTimeOut)));
        }

        $breakStart = Carbon::parse(
            $workDate->toDateString().' '.$breakStartValue,
            'Asia/Manila'
        );
        $breakEnd = Carbon::parse(
            $workDate->toDateString().' '.$breakEndValue,
            'Asia/Manila'
        );

        if ($breakEnd->lessThanOrEqualTo($breakStart)) {
            $breakEnd->addDay();
        }

        $overlapStart = $actualTimeIn->greaterThan($breakStart)
            ? $actualTimeIn->copy()
            : $breakStart->copy();
        $overlapEnd = $actualTimeOut->lessThan($breakEnd)
            ? $actualTimeOut->copy()
            : $breakEnd->copy();

        if ($overlapEnd->lessThanOrEqualTo($overlapStart)) {
            return 0;
        }

        return min(
            $configuredBreakMinutes,
            max(0, (int) ceil($overlapStart->floatDiffInMinutes($overlapEnd)))
        );
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
        if ($schedule && $this->scheduleIndicatesRestDay($schedule->status, $schedule->resolvedDayOffs(), $date)) {
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

    protected function resolveWorkdayRules(?EmployeePlottingSchedule $schedule): array
    {
        $defaultPaidMinutes = max(
            60,
            (int) config('payroll.attendance.paid_minutes_per_day', self::FULL_DAY_PAID_MINUTES)
        );
        $defaultLunchMinutes = max(
            0,
            (int) config('payroll.attendance.unpaid_break_minutes', 60)
        );

        $paidMinutes = $schedule?->paidWorkMinutes() ?? $defaultPaidMinutes;
        $lunchMinutes = $schedule?->lunchBreakMinutes() ?? $defaultLunchMinutes;
        $workdayType = $schedule?->resolvedWorkdayType()->value
            ?? ($paidMinutes >= 540 ? 'nine_hours' : 'eight_hours');

        return [
            'workday_type' => $workdayType,
            'paid_minutes_per_day' => max(60, (int) $paidMinutes),
            'lunch_break_minutes' => max(0, (int) $lunchMinutes),
            'scheduled_clock_minutes' => max(60, (int) $paidMinutes + (int) $lunchMinutes),
        ];
    }

    protected function normalizeDayOffValues(mixed $dayOff): array
    {
        if (is_string($dayOff)) {
            $decoded = json_decode($dayOff, true);
            $dayOff = is_array($decoded)
                ? $decoded
                : preg_split('/\s*,\s*/', $dayOff, -1, PREG_SPLIT_NO_EMPTY);
        }

        if (! is_array($dayOff)) {
            return [];
        }

        $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return collect($dayOff)
            ->map(fn (mixed $day): string => ucfirst(strtolower(trim((string) $day))))
            ->filter(fn (string $day): bool => in_array($day, $validDays, true))
            ->unique()
            ->values()
            ->all();
    }

    protected function scheduleIndicatesRestDay(?string $scheduleStatus, mixed $dayOff, Carbon $workDate): bool
    {
        $status = strtolower(trim((string) $scheduleStatus));

        if (in_array($status, ['rest_day', 'rest day', 'restday', 'day_off', 'day off', 'off'], true)) {
            return true;
        }

        $dayOffs = $this->normalizeDayOffValues($dayOff);

        if ($dayOffs === []) {
            return false;
        }

        return in_array($workDate->format('l'), $dayOffs, true);
    }

    protected function scheduleIndicatesLeave(?string $scheduleStatus): bool
    {
        $status = strtolower(trim((string) $scheduleStatus));

        return in_array($status, ['leave', 'on_leave', 'paid_leave', 'sick_leave', 'vacation_leave'], true)
            || str_contains($status, 'leave');
    }

    protected function isAutomaticHalfDay(?Carbon $actualTimeIn, ?Carbon $actualTimeOut): bool
    {
        if (($actualTimeIn && ! $actualTimeOut) || (! $actualTimeIn && $actualTimeOut)) {
            return true;
        }

        if (! $actualTimeIn || ! $actualTimeOut) {
            return false;
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
        $employeeBiometricId = ! empty($person['employee_biometric_id'])
            ? (int) $person['employee_biometric_id']
            : null;
        $table = (new EmployeePlottingSchedule)->getTable();

        /*
         * Canonical FK wins. Do not OR it with legacy identifiers because
         * source employee IDs can be reused by different CrossChex accounts.
         */
        if ($employeeBiometricId !== null && $this->columnExists($table, 'employee_biometric_id')) {
            $query->where('employee_biometric_id', $employeeBiometricId);

            return;
        }

        $identities = $this->personIdentityValues($person);

        if (empty($identities)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function ($q) use ($identities, $table): void {
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
        $crosschexAccount = $this->cleanString($person['source_crosschex_account'] ?? null);

        if ($crosschexAccount !== '' && $this->columnExists($table, 'crosschex_account')) {
            $query->where('crosschex_account', $crosschexAccount);
        }

        if (empty($identities)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function ($q) use ($identities, $table): void {
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
        $employeeBiometricId = ! empty($person['employee_biometric_id'])
            ? (int) $person['employee_biometric_id']
            : null;
        $table = (new PayrollAttendanceAdjustment)->getTable();

        if ($employeeBiometricId !== null && $this->columnExists($table, 'employee_biometric_id')) {
            $query->where('employee_biometric_id', $employeeBiometricId);

            return;
        }

        $identities = $this->personIdentityValues($person);

        if (empty($identities)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function ($q) use ($identities, $table): void {
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
            $person['source_employee_id'] ?? null,
            $person['source_employee_no'] ?? null,
            $person['source_crosschex_id'] ?? null,
            $person['source_key'] ?? null,
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
        if (! empty($person['employee_biometric_id'])) {
            return 'EMPLOYEE_BIOMETRIC:'.(int) $person['employee_biometric_id'];
        }

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
