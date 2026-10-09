<?php

declare(strict_types=1);

namespace App\Services\Biometrics;

use App\Models\EmployeePlottingSchedule;
use App\Models\MirasolBiometricsLog;
use App\Repositories\Contracts\Biometrics\BiometricsLogRepositoryInterface;
use App\Repositories\Contracts\Scheduling\PlottingScheduleRepositoryInterface;
use App\Services\Payroll\PayrollPeriodService;
use App\Support\PayrollEmployeeNameFormatter;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * Biometrics Sync page: for the people matching a search, one row per cutoff day with the plotted
 * schedule, first / last punch, worked hours, late, undertime and an attendance note.
 * Cutoff keys here are `26_10` (business 1st cutoff) and `11_25` (business 2nd cutoff).
 */
final class BiometricsAttendanceService
{
    public function __construct(
        private readonly BiometricsLogRepositoryInterface $logs,
        private readonly PlottingScheduleRepositoryInterface $schedules,
        private readonly PayrollPeriodService $periods,
    ) {}

    /** @return array{0: int, 1: int, 2: string} month, year, cutoff key for today */
    public function defaultCutoff(): array
    {
        [$month, $year, $type] = $this->periods->getDefaultCutoff();

        return [$month, $year, $type === 'first' ? '11_25' : '26_10'];
    }

    /** @return array{0: CarbonInterface, 1: CarbonInterface, 2: string} start, end and label */
    public function cutoffRange(int $year, int $month, string $cutoffType): array
    {
        $key = $cutoffType === '11_25' ? '11_25' : '26_10';
        [$start, $end] = $this->periods->resolveCutoffRange($month, $year, $key === '11_25' ? 'first' : 'second');
        $label = $start->format('F d, Y').' - '.$end->format('F d, Y').' | '
            .config("payroll.cutoff_display_by_range.{$key}", $key === '11_25' ? '2nd Cutoff (11-25)' : '1st Cutoff (26-10)');

        return [$start, $end, $label];
    }

    /** @return Collection<int, array{employee_no: ?string, employee_name: ?string, biometric_employee_id: ?string}> everyone in the logs or schedules, by display name */
    public function people(): Collection
    {
        return $this->uniquePeople($this->logs->people()->merge($this->schedules->people()));
    }

    /**
     * Attendance rows of everyone matching the search, by name then date.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function rows(string $search, CarbonInterface $start, CarbonInterface $end): Collection
    {
        $people = $this->uniquePeople($this->logs->people($search)->merge($this->schedules->people($search)));
        if ($people->isEmpty()) {
            return collect();
        }

        $employeeNos = $people->pluck('employee_no')->map(fn (mixed $value): string => trim((string) $value))->filter()->unique()->values();
        $biometricIds = $people->pluck('biometric_employee_id')->map(fn (mixed $value): string => trim((string) $value))->filter()->unique()->values();
        $logs = $this->logsByEmployeeDate($this->logs->forPeople($employeeNos, $biometricIds, $start, $end));
        [$dated, $permanent] = $this->schedulesByKey($this->schedules->forPeople($employeeNos, $biometricIds), $start, $end);

        $rows = collect();
        foreach ($people as $person) {
            $employeeKey = self::employeeKey($person['employee_no'] ?? null, $person['biometric_employee_id'] ?? null);

            foreach (CarbonPeriod::create($start->copy()->startOfDay(), $end) as $date) {
                $day = $date->toDateString();
                $schedule = ($dated->get($employeeKey.'_'.$day) ?? $permanent->get($employeeKey))?->forDate($day);
                $log = $logs->get($employeeKey.'_'.$day);

                $row = [
                    'employee_key' => $employeeKey,
                    'biometric_employee_id' => $person['biometric_employee_id'] ?? null,
                    'employee_no' => $person['employee_no'] ?? null,
                    'employee_name' => $person['employee_name'] ?? null,
                    'log_date' => $day,
                    'has_schedule' => false,
                    'schedule_status' => null,
                    'shift_name' => null,
                    'flexible_mode' => null,
                    'scheduled_time_in' => null,
                    'scheduled_time_out' => null,
                    'grace_minutes' => 15,
                    'day_off' => null,
                    'paid_work_minutes' => 480,
                    'required_clock_minutes' => 540,
                    'remarks' => null,
                    'actual_time_in' => $log['actual_time_in'] ?? null,
                    'actual_time_out' => $log['actual_time_out'] ?? null,
                    'log_count' => $log['log_count'] ?? 0,
                    'has_logs' => $log !== null,
                ];

                if ($schedule !== null) {
                    $row = array_merge($row, $this->schedulePayload($schedule, Carbon::parse($day)));
                }

                $rows->push($this->decorate($row));
            }
        }

        return $rows
            ->sortBy(fn (array $row): string => strtolower(PayrollEmployeeNameFormatter::display($row['employee_name'] ?? null)).'|'.$row['log_date'])
            ->values();
    }

    /** `EMPNO:<no>` when there is an employee number, else `BIO:<id>`. */
    public static function employeeKey(mixed $employeeNo, mixed $biometricId): string
    {
        $employeeNo = trim((string) $employeeNo);
        $biometricId = trim((string) $biometricId);

        return $employeeNo !== '' ? 'EMPNO:'.$employeeNo : 'BIO:'.($biometricId !== '' ? $biometricId : 'UNKNOWN');
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $people
     * @return Collection<int, array<string, mixed>>
     */
    private function uniquePeople(Collection $people): Collection
    {
        return $people
            ->filter(fn (array $row): bool => ! empty($row['employee_name']) || ! empty($row['employee_no']))
            ->unique(fn (array $row): string => self::employeeKey($row['employee_no'] ?? null, $row['biometric_employee_id'] ?? null))
            ->sortBy(fn (array $row): string => strtolower(PayrollEmployeeNameFormatter::display($row['employee_name'] ?? null)))
            ->values();
    }

    /**
     * First and last punch per person and day (minutes), keyed `<employee key>_<date>`.
     *
     * @param  Collection<int, MirasolBiometricsLog>  $logs
     * @return Collection<string, array{actual_time_in: ?string, actual_time_out: ?string, log_count: int}>
     */
    private function logsByEmployeeDate(Collection $logs): Collection
    {
        return $logs
            ->groupBy(fn (MirasolBiometricsLog $log): string => self::employeeKey($log->employee_no, $log->employee_id).'_'.Carbon::parse($log->check_time)->toDateString())
            ->map(function (Collection $group): array {
                $sorted = $group->sortBy('check_time')->values();
                $first = Carbon::parse($sorted->first()->check_time)->startOfMinute();
                $last = Carbon::parse($sorted->last()->check_time)->startOfMinute();

                return [
                    'actual_time_in' => $first->toDateTimeString(),
                    'actual_time_out' => $sorted->count() > 1 ? $last->toDateTimeString() : null,
                    'log_count' => $group->count(),
                ];
            });
    }

    /**
     * Dated schedules in the cutoff (keyed `<employee key>_<date>`) and the latest schedule per person.
     *
     * @param  Collection<int, EmployeePlottingSchedule>  $schedules
     * @return array{0: Collection<string, EmployeePlottingSchedule>, 1: Collection<string, EmployeePlottingSchedule>}
     */
    private function schedulesByKey(Collection $schedules, CarbonInterface $start, CarbonInterface $end): array
    {
        $key = fn (EmployeePlottingSchedule $schedule): string => self::employeeKey($schedule->employee_no, $schedule->biometric_employee_id);

        $dated = $schedules
            ->filter(fn (EmployeePlottingSchedule $schedule): bool => ! empty($schedule->work_date))
            ->filter(function (EmployeePlottingSchedule $schedule) use ($start, $end): bool {
                $day = Carbon::parse($schedule->work_date)->toDateString();

                return $day >= $start->toDateString() && $day <= $end->toDateString();
            })
            ->keyBy(fn (EmployeePlottingSchedule $schedule): string => $key($schedule).'_'.Carbon::parse($schedule->work_date)->toDateString());

        return [$dated, $schedules->unique($key)->keyBy($key)];
    }

    /** @return array<string, mixed> */
    private function schedulePayload(EmployeePlottingSchedule $schedule, Carbon $date): array
    {
        $dayOffs = $schedule->resolvedDayOffs();
        $time = fn (mixed $value): ?string => empty($value) ? null : Carbon::parse($value)->format('H:i');

        return [
            'has_schedule' => true,
            'schedule_status' => $schedule->isDayOffOn($date) ? 'rest_day' : ($schedule->status ?: 'scheduled'),
            'shift_name' => $schedule->shift_name ?: 'Regular Shift',
            'flexible_mode' => $schedule->resolvedFlexibleMode(),
            'scheduled_time_in' => $time($schedule->time_in),
            'scheduled_time_out' => $time($schedule->time_out),
            'grace_minutes' => (int) ($schedule->grace_minutes ?? 15),
            'day_off' => $dayOffs !== [] ? implode(', ', $dayOffs) : null,
            'paid_work_minutes' => $schedule->paidWorkMinutes(),
            'required_clock_minutes' => $schedule->requiredClockMinutes(),
            'remarks' => $schedule->remarks,
        ];
    }

    /**
     * Adds worked hours, late, undertime and the attendance note/tone to a row.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function decorate(array $row): array
    {
        $date = Carbon::parse($row['log_date']);
        $status = $row['schedule_status'];
        $isFlexible = str_contains(strtolower((string) $row['shift_name']), 'flexible');
        $flexibleMode = $row['flexible_mode'] ?? null;
        $isFlexibleCondition = $isFlexible && $flexibleMode === 'condition';
        $isFlexibleCustom = $isFlexible && $flexibleMode === 'custom';
        $scheduledIn = ! empty($row['scheduled_time_in']) ? Carbon::parse($date->toDateString().' '.$row['scheduled_time_in']) : null;
        $scheduledOut = ! empty($row['scheduled_time_out']) ? Carbon::parse($date->toDateString().' '.$row['scheduled_time_out']) : null;
        if ($scheduledIn && $scheduledOut && $scheduledOut->lessThanOrEqualTo($scheduledIn)) {
            $scheduledOut->addDay();
        }
        $actualIn = ! empty($row['actual_time_in']) ? Carbon::parse($row['actual_time_in'])->startOfMinute() : null;
        $actualOut = ! empty($row['actual_time_out']) ? Carbon::parse($row['actual_time_out'])->startOfMinute() : null;
        $grace = (int) $row['grace_minutes'];
        $requiredMinutes = max(60, (int) $row['required_clock_minutes']);

        $late = 0;
        $undertime = 0;
        $worked = null;
        if ($actualIn && $actualOut) {
            $minutes = (int) $actualIn->diffInMinutes($actualOut, false);
            $worked = $minutes > 0 ? $minutes : null;
        }

        [$note, $tone] = ['No attendance remark.', 'secondary'];

        if (! $row['has_schedule']) {
            [$note, $tone] = $row['has_logs'] ? ['No plotted schedule found.', 'warning'] : ['No schedule and no biometric log.', 'secondary'];
        } elseif (in_array($status, ['rest_day', 'leave', 'holiday'], true)) {
            $label = ucwords(str_replace('_', ' ', (string) $status));
            [$note, $tone] = $row['has_logs'] ? ['Biometric log detected on '.$label.'.', 'info'] : [$label, 'secondary'];
        } elseif ($status === 'scheduled') {
            if (! $row['has_logs']) {
                [$note, $tone] = ['Absent', 'danger'];
            } elseif ((int) $row['log_count'] < 2) {
                [$note, $tone] = ['Incomplete biometric logs.', 'warning'];
            } elseif ($isFlexibleCondition) {
                if ($worked === null) {
                    [$note, $tone] = ['Incomplete biometric logs.', 'warning'];
                } else {
                    if ($scheduledOut && $actualIn && $actualIn->gt($scheduledOut->copy()->addMinutes($grace))) {
                        $late = (int) $scheduledOut->diffInMinutes($actualIn);
                    }
                    if ($worked < $requiredMinutes) {
                        $undertime = $requiredMinutes - $worked;
                    }
                    $parts = array_filter([$late > 0 ? 'Late (outside clock-in window)' : null, $undertime > 0 ? 'Incomplete Flexible Hours' : null]);
                    [$note, $tone] = $parts === []
                        ? ['Completed Flexible '.round($requiredMinutes / 60, 2).' Clock Hours', 'success']
                        : [implode(' / ', $parts), 'warning'];
                }
            } elseif ($isFlexible && ! $isFlexibleCustom) {
                if ($worked === null) {
                    [$note, $tone] = ['Incomplete biometric logs.', 'warning'];
                } elseif ($worked >= $requiredMinutes) {
                    [$note, $tone] = ['Completed Flexible '.round($requiredMinutes / 60, 2).' Clock Hours', 'success'];
                } else {
                    $undertime = $requiredMinutes - $worked;
                    [$note, $tone] = ['Incomplete Flexible Hours', 'warning'];
                }
            } elseif (! $scheduledIn || ! $scheduledOut) {
                [$note, $tone] = [$isFlexibleCustom ? 'Flexible Shift (Custom) needs plotted Time In and Time Out.' : 'Regular Shift needs plotted Time In and Time Out.', 'warning'];
            } else {
                $allowedIn = $scheduledIn->copy()->addMinutes($grace);
                if ($actualIn && $actualIn->gt($allowedIn)) {
                    $late = (int) $allowedIn->diffInMinutes($actualIn);
                }
                if ($actualOut && $actualOut->lt($scheduledOut)) {
                    $undertime = (int) $actualOut->diffInMinutes($scheduledOut);
                }
                $parts = array_filter([$late > 0 ? 'Late' : null, $undertime > 0 ? 'Undertime' : null]);
                [$note, $tone] = [$parts === [] ? 'On Time' : implode(' / ', $parts), $parts === [] ? 'success' : 'warning'];
            }
        }

        return [
            ...$row,
            'shift_mode' => $isFlexible ? ('Flexible ('.ucfirst((string) ($flexibleMode ?: 'anytime')).')') : 'Regular',
            'required_hours_label' => $isFlexible && ! $isFlexibleCustom ? self::hours($requiredMinutes) : '—',
            'late_minutes' => $late,
            'undertime_minutes' => $undertime,
            'worked_hours_label' => self::hours($worked),
            'late_label' => $late > 0 ? self::hours($late) : '—',
            'undertime_label' => $undertime > 0 ? self::hours($undertime) : '—',
            'attendance_note' => $note,
            'attendance_class' => $tone,
        ];
    }

    /** `HH:MM`, or "—" for none. */
    private static function hours(?int $minutes): string
    {
        return $minutes === null || $minutes <= 0 ? '—' : sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
