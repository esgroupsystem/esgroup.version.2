<?php

namespace App\Services\Payroll;

use App\Enums\WorkdayType;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use Illuminate\Support\Facades\DB;

class EmployeePlottingScheduleService
{
    public function __construct(
        private readonly EmployeeBiometricIdentityService $identityService
    ) {}

    public function savePermanentSchedules(array $rows): void
    {
        DB::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                $this->savePermanentSchedule($row);
            }
        });
    }

    private function savePermanentSchedule(array $row): void
    {
        $employee = EmployeeBiometric::query()
            ->whereKey((int) $row['employee_biometric_id'])
            ->lockForUpdate()
            ->first();

        if (! $employee) {
            return;
        }

        $snapshot = $this->identityService->snapshot($employee);
        $status = (string) ($row['status'] ?? 'scheduled');
        $shiftName = (string) ($row['shift_name'] ?? 'Regular Shift');
        $workdayType = WorkdayType::from((string) ($row['workday_type'] ?? WorkdayType::EightHours->value));
        $dayOffs = $this->normalizeDayOffs($row['day_offs'] ?? []);

        $isFlexible = $shiftName === 'Flexible Shift';
        $isNonWorkingStatus = in_array($status, ['rest_day', 'inactive'], true);

        if ($status === 'inactive') {
            $employee->markPayrollInactive(
                $row['remarks'] ?? 'Marked inactive from permanent work schedule.'
            );
        }

        $payload = [
            'employee_biometric_id' => $employee->id,
            'crosschex_id' => $snapshot['crosschex_id'],
            'biometric_employee_id' => $snapshot['biometric_employee_id'],
            'employee_no' => $snapshot['employee_no'],
            'employee_name' => $snapshot['employee_name'],
            'work_date' => null,
            'shift_name' => $shiftName,
            'workday_type' => $workdayType->value,
            'paid_work_minutes' => $workdayType->paidMinutes(),
            'lunch_break_minutes' => $workdayType->lunchMinutes(),
            'time_in' => ($isFlexible || $isNonWorkingStatus) ? null : ($row['time_in'] ?? null),
            'time_out' => ($isFlexible || $isNonWorkingStatus) ? null : ($row['time_out'] ?? null),
            'grace_minutes' => (int) ($row['grace_minutes'] ?? 15),
            'status' => $status,
            'day_offs' => $dayOffs,
            // Retained for legacy code and safe rollback.
            'day_off' => implode(',', $dayOffs) ?: null,
            'remarks' => $row['remarks'] ?? null,
        ];

        EmployeePlottingSchedule::query()
            ->where('employee_biometric_id', $employee->id)
            ->whereNull('work_date')
            ->delete();

        EmployeePlottingSchedule::query()->create($payload);
    }

    private function normalizeDayOffs(mixed $dayOffs): array
    {
        if (is_string($dayOffs)) {
            $dayOffs = explode(',', $dayOffs);
        }

        if (! is_array($dayOffs)) {
            return [];
        }

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
            ->map(fn (mixed $day): string => trim((string) $day))
            ->filter(fn (string $day): bool => in_array($day, $validDays, true))
            ->unique()
            ->sortBy(fn (string $day): int => array_search($day, $validDays, true))
            ->values()
            ->all();
    }
}
