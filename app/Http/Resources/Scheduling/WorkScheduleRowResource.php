<?php

declare(strict_types=1);

namespace App\Http\Resources\Scheduling;

use App\Enums\WorkdayType;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One editable row of the Work Schedule grid (`payroll/plotting/index`). A person without a
 * saved schedule gets the defaults (scheduled, Regular Shift, 8 hours, 15 min grace).
 * Load `permanentSchedule` first; pass the identity snapshot for the employee number.
 *
 * @mixin EmployeeBiometric
 */
final class WorkScheduleRowResource extends JsonResource
{
    /** @param array<string, mixed> $identity EmployeeBiometricIdentityService::snapshot() */
    public function __construct(
        EmployeeBiometric $employee,
        private readonly array $identity,
    ) {
        parent::__construct($employee);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var EmployeePlottingSchedule|null $schedule */
        $schedule = $this->permanentSchedule;

        return [
            'employee_biometric_id' => (int) $this->id,
            'name' => $this->payroll_display_name,
            'employee_no' => $this->identity['employee_no'] ?? null,
            'group_name' => $this->group_name !== null ? (string) $this->group_name : null,
            'schedule' => [
                'status' => $schedule->status ?? EmployeePlottingSchedule::DEFAULT_STATUS,
                'shift_name' => $schedule->shift_name ?? EmployeePlottingSchedule::REGULAR_SHIFT,
                'flexible_mode' => $schedule?->resolvedFlexibleMode() ?? EmployeePlottingSchedule::FLEXIBLE_MODE_ANYTIME,
                'workday_type' => $schedule?->resolvedWorkdayType()->value ?? WorkdayType::EightHours->value,
                'time_in' => $schedule?->time_in ? substr((string) $schedule->time_in, 0, 5) : null,
                'time_out' => $schedule?->time_out ? substr((string) $schedule->time_out, 0, 5) : null,
                // Empty object = the same time every day.
                'weekly_times' => (object) ($schedule?->weeklyTimes() ?? []),
                'grace_minutes' => $schedule->grace_minutes ?? EmployeePlottingSchedule::DEFAULT_GRACE_MINUTES,
                'day_offs' => $schedule?->resolvedDayOffs() ?? [],
                'remarks' => $schedule->remarks ?? '',
                'is_saved' => $schedule !== null,
            ],
        ];
    }
}
