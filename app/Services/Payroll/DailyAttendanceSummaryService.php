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

class DailyAttendanceSummaryService
{
    protected int $defaultDailyPayableMinutes = 480; // 8 hours

    protected int $defaultBreakMinutes = 60; // 1 hour break

    public function buildForDate(string|Carbon $date): void
    {
        $workDate = $date instanceof Carbon
            ? $date->copy()->startOfDay()
            : Carbon::parse($date, 'Asia/Manila')->startOfDay();

        $people = $this->collectPeopleForDate($workDate);

        foreach ($people as $person) {
            $this->buildForPersonDate($person, $workDate);
        }
    }

    public function buildForPeriod(string|Carbon $startDate, string|Carbon $endDate): void
    {
        $start = $startDate instanceof Carbon
            ? $startDate->copy()->startOfDay()
            : Carbon::parse($startDate, 'Asia/Manila')->startOfDay();

        $end = $endDate instanceof Carbon
            ? $endDate->copy()->startOfDay()
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

        EmployeePlottingSchedule::query()
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

        return $people->values();
    }

    public function buildForPersonDate(array $person, string|Carbon $date): DailyAttendanceSummary
    {
        $workDate = $date instanceof Carbon
            ? $date->copy()->startOfDay()
            : Carbon::parse($date, 'Asia/Manila')->startOfDay();

        $schedule = EmployeePlottingSchedule::query()
            ->whereDate('work_date', $workDate->toDateString())
            ->where(function ($q) use ($person) {
                $this->applySchedulePersonMatch($q, $person);
            })
            ->first();

        $logs = MirasolBiometricsLog::query()
            ->whereDate('check_time', $workDate->toDateString())
            ->where(function ($q) use ($person) {
                $this->applyLogPersonMatch($q, $person);
            })
            ->orderBy('check_time')
            ->get();

        $adjustment = PayrollAttendanceAdjustment::query()
            ->whereDate('work_date', $workDate->toDateString())
            ->where(function ($q) use ($person) {
                $this->applyAdjustmentPersonMatch($q, $person);
            })
            ->latest('id')
            ->first();

        $holiday = class_exists(Holiday::class)
            ? Holiday::query()
                ->when($this->columnExists('holidays', 'is_active'), fn ($q) => $q->where('is_active', true))
                ->where(function ($q) use ($workDate) {
                    if ($this->columnExists('holidays', 'observed_date')) {
                        $q->whereDate('observed_date', $workDate->toDateString());
                    } elseif ($this->columnExists('holidays', 'holiday_date')) {
                        $q->whereDate('holiday_date', $workDate->toDateString());
                    } elseif ($this->columnExists('holidays', 'date')) {
                        $q->whereDate('date', $workDate->toDateString());
                    } elseif ($this->columnExists('holidays', 'work_date')) {
                        $q->whereDate('work_date', $workDate->toDateString());
                    }
                })
                ->first()
            : null;

        return $this->storeSummary($person, $workDate, $schedule, $logs, $adjustment, $holiday);
    }

    protected function storeSummary(
        array $person,
        Carbon $workDate,
        ?EmployeePlottingSchedule $schedule,
        Collection $logs,
        ?PayrollAttendanceAdjustment $adjustment,
        $holiday
    ): DailyAttendanceSummary {
        $actualTimeIn = $logs->isNotEmpty()
            ? Carbon::parse($logs->first()->check_time, 'Asia/Manila')
            : null;

        $actualTimeOut = $logs->count() >= 2
            ? Carbon::parse($logs->last()->check_time, 'Asia/Manila')
            : null;

        $rawLogCount = $logs->count();
        $hasBiometrics = $rawLogCount > 0;
        $isIncompleteLog = $rawLogCount === 1;

        $firstLogState = $logs->first()?->state;
        $lastLogState = $logs->last()?->state;

        $scheduledTimeIn = $schedule?->time_in;
        $scheduledTimeOut = $schedule?->time_out;
        $graceMinutes = (int) ($schedule?->grace_minutes ?? 0);
        $scheduleStatus = $schedule?->status ?? 'scheduled';
        $scheduleRemarks = $schedule?->remarks;

        $effectiveScheduleStatus = $scheduleStatus;
        $effectiveScheduledTimeIn = $scheduledTimeIn;
        $effectiveScheduledTimeOut = $scheduledTimeOut;

        $isRestDay = $scheduleStatus === 'rest_day';
        $isLeave = $scheduleStatus === 'leave';

        $isHoliday = ! is_null($holiday);
        $holidayName = $holiday?->name;
        $holidayType = $holiday?->holiday_type;
        $holidayWorkedMultiplier = (float) ($holiday?->worked_multiplier ?? 0);
        $holidayNotWorkedMultiplier = (float) ($holiday?->not_worked_multiplier ?? 0);

        $hasAdjustment = ! is_null($adjustment);
        $adjustmentType = $adjustment?->adjustment_type;
        $adjustedTimeIn = $adjustment?->adjusted_time_in;
        $adjustedTimeOut = $adjustment?->adjusted_time_out;
        $adjustedDayType = $adjustment?->adjusted_day_type;
        $adjustmentIsPaid = (bool) ($adjustment?->is_paid ?? false);
        $ignoreLate = (bool) ($adjustment?->ignore_late ?? false);
        $ignoreUndertime = (bool) ($adjustment?->ignore_undertime ?? false);
        $adjustmentReason = $adjustment?->reason;
        $adjustmentRemarks = $adjustment?->remarks;

        $remarks = [];
        $forcedPresent = false;
        $forcedAbsent = false;

        if ($adjustment) {
            switch ($adjustmentType) {
                case 'change_schedule':
                case 'change_time':
                    if (! empty($adjustedTimeIn)) {
                        $effectiveScheduledTimeIn = $adjustedTimeIn;
                        $remarks[] = 'Adjusted scheduled time in applied.';
                    }

                    if (! empty($adjustedTimeOut)) {
                        $effectiveScheduledTimeOut = $adjustedTimeOut;
                        $remarks[] = 'Adjusted scheduled time out applied.';
                    }

                    if (! empty($adjustedDayType)) {
                        $effectiveScheduleStatus = $adjustedDayType;
                    }
                    break;

                case 'offset':
                    if (! empty($adjustedDayType)) {
                        $effectiveScheduleStatus = $adjustedDayType;
                    } else {
                        $effectiveScheduleStatus = 'rest_day';
                    }

                    if (! empty($adjustedTimeIn)) {
                        $effectiveScheduledTimeIn = $adjustedTimeIn;
                    }

                    if (! empty($adjustedTimeOut)) {
                        $effectiveScheduledTimeOut = $adjustedTimeOut;
                    }

                    $remarks[] = 'Offset adjustment applied.';
                    break;

                case 'rest_day_work':
                    $effectiveScheduleStatus = 'scheduled';

                    if (! empty($adjustedTimeIn)) {
                        $effectiveScheduledTimeIn = $adjustedTimeIn;
                    }

                    if (! empty($adjustedTimeOut)) {
                        $effectiveScheduledTimeOut = $adjustedTimeOut;
                    }

                    $isRestDay = false;
                    $remarks[] = 'Rest day work adjustment applied.';
                    break;

                case 'holiday_work':
                    $effectiveScheduleStatus = 'scheduled';

                    if (! empty($adjustedTimeIn)) {
                        $effectiveScheduledTimeIn = $adjustedTimeIn;
                    }

                    if (! empty($adjustedTimeOut)) {
                        $effectiveScheduledTimeOut = $adjustedTimeOut;
                    }

                    $remarks[] = 'Holiday work adjustment applied.';
                    break;

                case 'official_business':
                case 'training':
                    $forcedPresent = true;

                    if (! empty($adjustedDayType)) {
                        $effectiveScheduleStatus = $adjustedDayType;
                    }

                    if (! empty($adjustedTimeIn)) {
                        $effectiveScheduledTimeIn = $adjustedTimeIn;
                    }

                    if (! empty($adjustedTimeOut)) {
                        $effectiveScheduledTimeOut = $adjustedTimeOut;
                    }

                    $remarks[] = ucfirst(str_replace('_', ' ', $adjustmentType)).' adjustment applied.';
                    break;

                case 'manual_time_in_out':
                    if (! empty($adjustedTimeIn)) {
                        $actualTimeIn = Carbon::parse($workDate->toDateString().' '.$adjustedTimeIn, 'Asia/Manila');
                        $hasBiometrics = true;
                        $remarks[] = 'Manual adjusted time in applied.';
                    }

                    if (! empty($adjustedTimeOut)) {
                        $actualTimeOut = Carbon::parse($workDate->toDateString().' '.$adjustedTimeOut, 'Asia/Manila');
                        $hasBiometrics = true;
                        $remarks[] = 'Manual adjusted time out applied.';
                    }

                    if (! empty($adjustedTimeIn) && ! empty($adjustedTimeOut)) {
                        $isIncompleteLog = false;
                    }
                    break;

                case 'manual_present':
                    $forcedPresent = true;
                    $hasBiometrics = true;
                    $isIncompleteLog = false;
                    $remarks[] = 'Manual present adjustment applied.';
                    break;

                case 'manual_absent':
                    $forcedAbsent = true;
                    $hasBiometrics = false;
                    $actualTimeIn = null;
                    $actualTimeOut = null;
                    $isIncompleteLog = false;
                    $remarks[] = 'Manual absent adjustment applied.';
                    break;
            }

            if ($effectiveScheduleStatus === 'rest_day') {
                $isRestDay = true;
                $isLeave = false;
            } elseif ($effectiveScheduleStatus === 'leave') {
                $isLeave = true;
                $isRestDay = false;
            } else {
                $isLeave = false;
                $isRestDay = false;
            }
        }

        $scheduleIn = null;
        $scheduleOut = null;
        $scheduledSpanMinutes = 0;
        $scheduledPayableMinutes = $this->defaultDailyPayableMinutes;

        if ($effectiveScheduledTimeIn && $effectiveScheduledTimeOut) {
            $scheduleIn = Carbon::parse($workDate->toDateString().' '.$effectiveScheduledTimeIn, 'Asia/Manila');
            $scheduleOut = Carbon::parse($workDate->toDateString().' '.$effectiveScheduledTimeOut, 'Asia/Manila');

            if ($scheduleOut->lt($scheduleIn)) {
                $scheduleOut->addDay();
            }

            $scheduledSpanMinutes = max(0, $scheduleIn->diffInMinutes($scheduleOut));
            $scheduledPayableMinutes = $this->resolveScheduledPayableMinutes($scheduledSpanMinutes);
        }

        if ($actualTimeIn && $actualTimeOut && $actualTimeOut->lt($actualTimeIn)) {
            $actualTimeOut->addDay();
        }

        $lateMinutes = 0;
        $undertimeMinutes = 0;
        $workedMinutes = 0;
        $overtimeMinutes = 0;
        $isAbsent = false;
        $attendanceStatus = null;
        $payableDays = 0;
        $payableHours = 0;

        if (! $forcedAbsent && $actualTimeIn && $actualTimeOut) {
            if ($scheduleIn && $scheduleOut) {
                $allowedIn = $scheduleIn->copy()->addMinutes($graceMinutes);

                if ($actualTimeIn->gt($allowedIn)) {
                    $lateMinutes = $allowedIn->diffInMinutes($actualTimeIn);
                }

                if ($actualTimeOut->lt($scheduleOut)) {
                    $undertimeMinutes = $actualTimeOut->diffInMinutes($scheduleOut);
                }

                if ($actualTimeOut->gt($scheduleOut)) {
                    $overtimeMinutes = $scheduleOut->diffInMinutes($actualTimeOut);
                }

                $effectiveStart = $actualTimeIn->gt($scheduleIn) ? $actualTimeIn->copy() : $scheduleIn->copy();
                $effectiveEnd = $actualTimeOut->lt($scheduleOut) ? $actualTimeOut->copy() : $scheduleOut->copy();

                if ($effectiveEnd->gt($effectiveStart)) {
                    $workedMinutes = $effectiveStart->diffInMinutes($effectiveEnd);

                    if ($scheduledSpanMinutes > $this->defaultDailyPayableMinutes) {
                        $workedMinutes = max(0, $workedMinutes - $this->defaultBreakMinutes);
                    }
                }
            } else {
                $rawWorkedMinutes = max(0, $actualTimeIn->diffInMinutes($actualTimeOut));
                $workedMinutes = $this->resolveNetWorkedMinutes($rawWorkedMinutes);
            }
        }

        if ($ignoreLate) {
            $lateMinutes = 0;
            $remarks[] = 'Late ignored by adjustment.';
        }

        if ($ignoreUndertime) {
            $undertimeMinutes = 0;
            $remarks[] = 'Undertime ignored by adjustment.';
        }

        if ($forcedAbsent) {
            $attendanceStatus = 'absent';
            $isAbsent = true;
            $payableHours = 0;
            $payableDays = 0;
            $remarks[] = 'Forced absent by adjustment.';
        } elseif ($isLeave) {
            $attendanceStatus = 'leave';
            $payableHours = $adjustmentIsPaid ? round($scheduledPayableMinutes / 60, 2) : 0;
            $payableDays = $adjustmentIsPaid && $scheduledPayableMinutes > 0 ? 1 : 0;
            $remarks[] = 'Employee marked as leave.';
        } elseif ($isRestDay && ! $hasBiometrics && ! $forcedPresent) {
            $attendanceStatus = 'rest_day';
            $payableHours = 0;
            $payableDays = 0;
            $remarks[] = 'Rest day with no work.';
        } elseif (($isRestDay && $hasBiometrics) || $adjustmentType === 'rest_day_work') {
            $attendanceStatus = 'rest_day_worked';
            $payableHours = round($workedMinutes / 60, 2);
            $payableDays = 0;
            $remarks[] = 'Worked on rest day.';
        } elseif ($isHoliday && ! $hasBiometrics && ! $forcedPresent) {
            $attendanceStatus = 'holiday';

            // If holiday has no work but is still paid
            if ($holidayNotWorkedMultiplier > 0) {
                $payableHours = round($scheduledPayableMinutes / 60, 2);
                $payableDays = $scheduledPayableMinutes > 0 ? 1 : 0;
            } else {
                $payableHours = 0;
                $payableDays = 0;
            }

            $remarks[] = 'Holiday with no work.';
        } elseif (($isHoliday && $hasBiometrics) || $adjustmentType === 'holiday_work') {
            $attendanceStatus = 'holiday_worked';

            $payableHours = round($workedMinutes / 60, 2);

            if ($scheduledPayableMinutes > 0 && $workedMinutes > 0) {
                $payableDays = round($workedMinutes / $scheduledPayableMinutes, 4);
                if ($payableDays > 1) {
                    $payableDays = 1;
                }
            } else {
                $payableDays = 0;
            }

            $remarks[] = 'Worked on holiday.';
        } elseif ($forcedPresent && ! $actualTimeIn && ! $actualTimeOut) {
            $attendanceStatus = 'adjusted_present';
            $payableHours = round($scheduledPayableMinutes / 60, 2);
            $payableDays = $scheduledPayableMinutes > 0 ? 1 : 0;
            $remarks[] = 'Forced present by approved adjustment.';
        } elseif (! $hasBiometrics && $hasAdjustment && $adjustmentIsPaid) {
            $attendanceStatus = 'adjusted_present';
            $payableHours = round($scheduledPayableMinutes / 60, 2);
            $payableDays = $scheduledPayableMinutes > 0 ? 1 : 0;
            $remarks[] = 'Attendance based on approved paid adjustment.';
        } elseif (! $hasBiometrics && ! $hasAdjustment) {
            $attendanceStatus = 'absent';
            $isAbsent = true;
            $payableHours = 0;
            $payableDays = 0;
            $remarks[] = 'No biometrics and no adjustment.';
        } elseif ($isIncompleteLog) {
            $attendanceStatus = 'incomplete_log';
            $payableHours = 0;
            $payableDays = 0;
            $remarks[] = 'Incomplete biometric logs.';
        } else {
            if ($lateMinutes > 0 && $undertimeMinutes > 0) {
                $attendanceStatus = 'late_undertime';
            } elseif ($lateMinutes > 0) {
                $attendanceStatus = 'late';
            } elseif ($undertimeMinutes > 0) {
                $attendanceStatus = 'undertime';
            } else {
                $attendanceStatus = $hasAdjustment ? 'adjusted_present' : 'present';
            }

            if ($scheduledPayableMinutes > 0 && $workedMinutes > 0) {
                $payableHours = round($workedMinutes / 60, 2);
                
                if ($lateMinutes == 0 && $undertimeMinutes == 0) {
                    $payableDays = 1;
                } else {
                    $payableDays = round($workedMinutes / $scheduledPayableMinutes, 4);

                    if ($payableDays > 1) {
                        $payableDays = 1;
                    }
                }
            }

            $remarks[] = 'Payable computed from effective worked minutes.';
        }

        $identity = $this->makeSummaryIdentity($person, $workDate);

        return DailyAttendanceSummary::updateOrCreate(
            $identity,
            [
                'employee_id' => null,
                'biometric_employee_id' => $person['biometric_employee_id'],
                'employee_no' => $person['employee_no'],
                'employee_name' => $person['employee_name'] ?: 'Unknown Employee',
                'work_date' => $workDate->toDateString(),

                'plotting_schedule_id' => $schedule?->id,
                'attendance_adjustment_id' => $adjustment?->id,
                'holiday_id' => $holiday?->id,

                'crosschex_id' => $schedule?->crosschex_id,
                'shift_name' => $schedule?->shift_name,
                'scheduled_time_in' => $effectiveScheduledTimeIn,
                'scheduled_time_out' => $effectiveScheduledTimeOut,
                'grace_minutes' => $graceMinutes,
                'schedule_status' => $effectiveScheduleStatus,
                'schedule_remarks' => $scheduleRemarks,

                'actual_time_in' => $actualTimeIn,
                'actual_time_out' => $actualTimeOut,
                'raw_log_count' => $rawLogCount,
                'has_biometrics' => $hasBiometrics,
                'first_log_state' => $firstLogState,
                'last_log_state' => $lastLogState,

                'is_rest_day' => $isRestDay,
                'is_leave' => $isLeave,
                'is_holiday' => $isHoliday,
                'holiday_name' => $holidayName,
                'holiday_type' => $holidayType,
                'holiday_worked_multiplier' => $holidayWorkedMultiplier,
                'holiday_not_worked_multiplier' => $holidayNotWorkedMultiplier,

                'has_adjustment' => $hasAdjustment,
                'adjustment_type' => $adjustmentType,
                'adjusted_time_in' => $adjustedTimeIn,
                'adjusted_time_out' => $adjustedTimeOut,
                'adjusted_day_type' => $adjustedDayType,
                'adjustment_is_paid' => $adjustmentIsPaid,
                'ignore_late' => $ignoreLate,
                'ignore_undertime' => $ignoreUndertime,
                'adjustment_reason' => $adjustmentReason,
                'adjustment_remarks' => $adjustmentRemarks,

                'attendance_status' => $attendanceStatus,
                'late_minutes' => $lateMinutes,
                'undertime_minutes' => $undertimeMinutes,
                'worked_minutes' => $workedMinutes,
                'overtime_minutes' => $overtimeMinutes,

                'payable_days' => round($payableDays, 2),
                'payable_hours' => round($payableHours, 2),

                'is_absent' => $isAbsent,
                'is_incomplete_log' => $isIncompleteLog,

                'remarks' => implode(' ', $remarks),
                'computed_at' => now('Asia/Manila'),
            ]
        );
    }

    protected function resolveScheduledPayableMinutes(int $scheduledSpanMinutes): int
    {
        if ($scheduledSpanMinutes <= 0) {
            return $this->defaultDailyPayableMinutes;
        }

        if ($scheduledSpanMinutes > $this->defaultDailyPayableMinutes) {
            return max(0, $scheduledSpanMinutes - $this->defaultBreakMinutes);
        }

        return $scheduledSpanMinutes;
    }

    protected function resolveNetWorkedMinutes(int $rawWorkedMinutes): int
    {
        if ($rawWorkedMinutes <= 0) {
            return 0;
        }

        if ($rawWorkedMinutes > $this->defaultDailyPayableMinutes) {
            return max(0, $rawWorkedMinutes - $this->defaultBreakMinutes);
        }

        return $rawWorkedMinutes;
    }

    protected function makeSummaryIdentity(array $person, Carbon $workDate): array
    {
        $identity = [
            'work_date' => $workDate->toDateString(),
        ];

        if (! empty($person['biometric_employee_id'])) {
            $identity['biometric_employee_id'] = $person['biometric_employee_id'];

            return $identity;
        }

        if (! empty($person['employee_no'])) {
            $identity['employee_no'] = $person['employee_no'];

            return $identity;
        }

        $identity['employee_name'] = $person['employee_name'] ?: 'Unknown Employee';

        return $identity;
    }

    protected function applySchedulePersonMatch(Builder $query, array $person): void
    {
        $query->where(function ($q) use ($person) {
            $matched = false;

            if (! empty($person['biometric_employee_id'])) {
                $q->orWhere('biometric_employee_id', $person['biometric_employee_id']);
                $matched = true;
            }

            if (! empty($person['employee_no'])) {
                $q->orWhere('employee_no', $person['employee_no']);
                $matched = true;
            }

            if (! empty($person['employee_name'])) {
                $q->orWhere('employee_name', $person['employee_name']);
                $matched = true;
            }

            if (! $matched) {
                $q->whereRaw('1 = 0');
            }
        });
    }

    protected function applyLogPersonMatch(Builder $query, array $person): void
    {
        $query->where(function ($q) use ($person) {
            if (! empty($person['employee_no'])) {
                $q->orWhere('employee_no', $person['employee_no']);
            }

            if (! empty($person['biometric_employee_id'])) {
                $q->orWhere('employee_id', $person['biometric_employee_id']);
            }

            if (! empty($person['employee_name'])) {
                $q->orWhere('employee_name', $person['employee_name']);
            }
        });
    }

    protected function applyAdjustmentPersonMatch(Builder $query, array $person): void
    {
        $query->where(function ($q) use ($person) {
            $matched = false;

            if (! empty($person['biometric_employee_id'])) {
                $q->orWhere('biometric_employee_id', $person['biometric_employee_id']);
                $matched = true;
            }

            if (! empty($person['employee_no'])) {
                $q->orWhere('employee_no', $person['employee_no']);
                $matched = true;
            }

            if (! empty($person['employee_name'])) {
                $q->orWhere('employee_name', $person['employee_name']);
                $matched = true;
            }

            if (! $matched) {
                $q->whereRaw('1 = 0');
            }
        });
    }

    protected function makePersonKey(array $person): string
    {
        if (! empty($person['employee_no'])) {
            return 'EMP:'.$person['employee_no'];
        }

        if (! empty($person['biometric_employee_id'])) {
            return 'BIO:'.$person['biometric_employee_id'];
        }

        return 'NAME:'.mb_strtoupper($person['employee_name'] ?: 'UNKNOWN');
    }

    protected function cleanString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function columnExists(string $table, string $column): bool
    {
        try {
            return \Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
