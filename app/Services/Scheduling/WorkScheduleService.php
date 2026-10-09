<?php

declare(strict_types=1);

namespace App\Services\Scheduling;

use App\Enums\WorkdayType;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Repositories\Contracts\Biometrics\EmployeeBiometricRepositoryInterface;
use App\Repositories\Contracts\Scheduling\PlottingScheduleRepositoryInterface;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Scheduling & Rates → Work Schedule: one permanent schedule per payroll-active person
 * (status, shift, workday type, time in/out, grace, days off).
 */
final class WorkScheduleService
{
    public function __construct(
        private readonly EmployeeBiometricRepositoryInterface $biometrics,
        private readonly PlottingScheduleRepositoryInterface $schedules,
        private readonly EmployeeBiometricIdentityService $identity,
    ) {}

    /**
     * Payroll-active people with their permanent schedule; the status and shift filters
     * run in the query, so every page is full and the totals are right.
     *
     * @return LengthAwarePaginator<int, EmployeeBiometric>
     */
    public function paginate(string $search, string $group, string $status, string $shift): LengthAwarePaginator
    {
        return $this->biometrics->paginateForSchedule($search, $group, $status, $shift);
    }

    /** @return Collection<int, string> payroll groups of payroll-active people */
    public function groups(): Collection
    {
        return $this->biometrics->groups(payrollActiveOnly: true);
    }

    /**
     * Count cards. Schedule counts describe the people on the current page.
     *
     * @param  LengthAwarePaginator<int, EmployeeBiometric>  $page
     * @return array<string, int>
     */
    public function stats(LengthAwarePaginator $page): array
    {
        $saved = $page->getCollection()->pluck('permanentSchedule')->filter();
        $workday = fn (WorkdayType $type): int => $saved->filter(fn (EmployeePlottingSchedule $schedule): bool => $schedule->resolvedWorkdayType() === $type)->count();

        return [
            'visible_employees' => $page->count(),
            'saved_permanent' => $saved->count(),
            'scheduled' => $saved->where('status', 'scheduled')->count(),
            'rest_day' => $saved->where('status', 'rest_day')->count(),
            'inactive' => $this->biometrics->countInactive(),
            'regular' => $saved->where('shift_name', EmployeePlottingSchedule::REGULAR_SHIFT)->count(),
            'flexible' => $saved->where('shift_name', EmployeePlottingSchedule::FLEXIBLE_SHIFT)->count(),
            'eight_hours' => $workday(WorkdayType::EightHours),
            'nine_hours' => $workday(WorkdayType::NineHours),
            'straight_eight' => $workday(WorkdayType::StraightEightHours),
        ];
    }

    /** @return array<string, array<string, mixed>> workday type => hours rules, for the editor */
    public function workdayRules(): array
    {
        return collect(WorkdayType::cases())
            ->mapWithKeys(fn (WorkdayType $type): array => [
                $type->value => [
                    'label' => $type->label(),
                    'short_label' => $type->shortLabel(),
                    'paid_hours' => $type->paidHours(),
                    'paid_minutes' => $type->paidMinutes(),
                    'lunch_minutes' => $type->lunchMinutes(),
                    'clock_minutes' => $type->clockMinutes(),
                ],
            ])
            ->all();
    }

    /** @return array{employee_no: ?string, biometric_employee_id: ?string, crosschex_id: ?string, employee_name: ?string} */
    public function identity(EmployeeBiometric $employee): array
    {
        return $this->identity->snapshot($employee);
    }

    /**
     * Saves every row in one transaction. "inactive" also takes the person off payroll.
     *
     * @param  list<array<string, mixed>>  $rows
     */
    public function savePermanentSchedules(array $rows): void
    {
        DB::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                $this->savePermanentSchedule($row);
            }
        });
    }

    /** @param array<string, mixed> $row */
    /**
     * "Different time per day" entries for working days only (days off are dropped).
     * Null when empty, so the schedule is the same every day.
     *
     * @param  list<string>  $dayOffs
     * @return array<string, array{time_in: string, time_out: string, workday_type: string}>|null
     */
    private function weeklyTimes(mixed $weekly, array $dayOffs): ?array
    {
        $times = [];

        foreach (EmployeePlottingSchedule::WEEKDAYS as $day) {
            $entry = is_array($weekly) ? ($weekly[$day] ?? null) : null;

            if (! is_array($entry) || in_array($day, $dayOffs, true)) {
                continue;
            }

            $times[$day] = [
                'time_in' => substr((string) $entry['time_in'], 0, 5),
                'time_out' => substr((string) $entry['time_out'], 0, 5),
                'workday_type' => WorkdayType::from((string) $entry['workday_type'])->value,
            ];
        }

        return $times === [] ? null : $times;
    }

    /**
     * Flexible Shift (Custom) shift-time options, e.g. an employee who may clock in for
     * either an 8:00 AM or a 9:00 AM shift. Invalid or empty entries are dropped.
     *
     * @return list<array{time_in: string, time_out: string}>
     */
    private function normalizeShiftOptions(mixed $options): array
    {
        $normalized = [];

        foreach (is_array($options) ? $options : [] as $entry) {
            if (! is_array($entry) || empty($entry['time_in']) || empty($entry['time_out'])) {
                continue;
            }

            $normalized[] = [
                'time_in' => substr((string) $entry['time_in'], 0, 5),
                'time_out' => substr((string) $entry['time_out'], 0, 5),
            ];
        }

        return array_slice($normalized, 0, EmployeePlottingSchedule::MAX_FLEXIBLE_SHIFT_OPTIONS);
    }

    private function savePermanentSchedule(array $row): void
    {
        $employee = $this->biometrics->findForUpdate((int) $row['employee_biometric_id']);
        if ($employee === null) {
            return;
        }

        $snapshot = $this->identity->snapshot($employee);
        $status = (string) ($row['status'] ?? EmployeePlottingSchedule::DEFAULT_STATUS);
        $shiftName = (string) ($row['shift_name'] ?? EmployeePlottingSchedule::REGULAR_SHIFT);
        $isFlexible = $shiftName === EmployeePlottingSchedule::FLEXIBLE_SHIFT;
        $flexibleModeInput = (string) ($row['flexible_mode'] ?? EmployeePlottingSchedule::FLEXIBLE_MODE_ANYTIME);
        $flexibleMode = in_array($flexibleModeInput, EmployeePlottingSchedule::FLEXIBLE_MODES, true)
            ? $flexibleModeInput
            : EmployeePlottingSchedule::FLEXIBLE_MODE_ANYTIME;
        $workdayType = WorkdayType::from((string) ($row['workday_type'] ?? WorkdayType::EightHours->value));
        $dayOffs = $this->normalizeDayOffs($row['day_offs'] ?? []);
        $isCustomFlexible = $isFlexible && $flexibleMode === EmployeePlottingSchedule::FLEXIBLE_MODE_CUSTOM;
        $noClock = ($isFlexible && $flexibleMode === EmployeePlottingSchedule::FLEXIBLE_MODE_ANYTIME)
            || in_array($status, ['rest_day', 'inactive'], true);
        $shiftOptions = $isCustomFlexible && ! $noClock
            ? $this->normalizeShiftOptions($row['flexible_shift_options'] ?? null)
            : [];
        $firstOption = $shiftOptions[0] ?? null;

        if ($status === 'inactive') {
            $employee->markPayrollInactive($row['remarks'] ?? 'Marked inactive from permanent work schedule.');
        }

        $this->schedules->replacePermanent($employee->id, [
            'crosschex_id' => $snapshot['crosschex_id'],
            'biometric_employee_id' => $snapshot['biometric_employee_id'],
            'employee_no' => $snapshot['employee_no'],
            'employee_name' => $snapshot['employee_name'],
            'shift_name' => $shiftName,
            'flexible_mode' => $isFlexible ? $flexibleMode : null,
            'flexible_shift_options' => $isCustomFlexible ? $shiftOptions : null,
            'workday_type' => $workdayType->value,
            'paid_work_minutes' => $workdayType->paidMinutes(),
            'lunch_break_minutes' => $workdayType->lunchMinutes(),
            'time_in' => $isCustomFlexible ? ($firstOption['time_in'] ?? null) : ($noClock ? null : ($row['time_in'] ?? null)),
            'time_out' => $isCustomFlexible ? ($firstOption['time_out'] ?? null) : ($noClock ? null : ($row['time_out'] ?? null)),
            // "Different time per day" only applies to Regular Shift; Flexible Shift (Custom) uses flexible_shift_options instead.
            'weekly_times' => $shiftName === EmployeePlottingSchedule::REGULAR_SHIFT && ! $noClock
                ? $this->weeklyTimes($row['weekly_times'] ?? null, $dayOffs)
                : null,
            'grace_minutes' => (int) ($row['grace_minutes'] ?? EmployeePlottingSchedule::DEFAULT_GRACE_MINUTES),
            'status' => $status,
            'day_offs' => $dayOffs,
            // Retained for legacy code and safe rollback.
            'day_off' => implode(',', $dayOffs) ?: null,
            'remarks' => $row['remarks'] ?? null,
        ]);
    }

    /** @return list<string> known weekdays, unique, Monday first */
    private function normalizeDayOffs(mixed $dayOffs): array
    {
        if (is_string($dayOffs)) {
            $dayOffs = explode(',', $dayOffs);
        }
        if (! is_array($dayOffs)) {
            return [];
        }

        return collect($dayOffs)
            ->map(fn (mixed $day): string => trim((string) $day))
            ->filter(fn (string $day): bool => in_array($day, EmployeePlottingSchedule::WEEKDAYS, true))
            ->unique()
            ->sortBy(fn (string $day): int => (int) array_search($day, EmployeePlottingSchedule::WEEKDAYS, true))
            ->values()
            ->all();
    }
}
