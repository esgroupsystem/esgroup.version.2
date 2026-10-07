<?php

declare(strict_types=1);

namespace App\Http\Resources\Scheduling;

use App\Models\EmployeeBiometric;
use App\Services\Payroll\PayrollGroupAccessService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of Scheduling & Rates → Employees (`biometrics/employees/index`).
 * Load `company` and `hrEmployee` first; `permanentSchedule` and `activeSalaryProfile` for the
 * schedule / rate summary (the rate only shows to users who may see it).
 *
 * @mixin EmployeeBiometric
 */
final class BiometricEmployeeRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $isActive = $this->employment_status === EmployeeBiometric::STATUS_ACTIVE;

        return [
            'id' => $this->id,
            'display_name' => $this->payroll_display_name,
            'display_no' => $this->display_employee_no ?: 'N/A',
            'source_no' => $this->source_employee_no ?: 'N/A',
            'group_label' => EmployeeBiometric::GROUP_LABELS[(int) $this->group_name] ?? null,
            'company' => $this->company?->name,
            'active' => $isActive,
            'payroll_included' => $isActive && (bool) ($this->is_payroll_active ?? true),
            'device_name' => $this->device_name ?: 'N/A',
            'device_sn' => $this->device_sn ?: 'N/A',
            'last_check_date' => $this->last_check_time?->format('M d, Y'),
            'last_check_time' => $this->last_check_time?->format('h:i A'),
            'total_logs' => (int) ($this->total_logs ?? 0),
            'hr_employee' => $this->hrEmployee ? [
                'name' => (string) $this->hrEmployee->full_name,
                'url' => route('employees.staff.show', $this->hrEmployee->id),
            ] : null,
            'edit_url' => route('biometrics.employees.edit', $this->resource),
            'show_url' => route('biometrics.employees.show', $this->resource),
            'schedule' => $this->scheduleSummary(),
            'rate' => $this->rateSummary($request),
        ];
    }

    /** @return array{label: string, hours: string|null}|null null = no saved Work Schedule */
    private function scheduleSummary(): ?array
    {
        if (! $this->relationLoaded('permanentSchedule') || $this->permanentSchedule === null) {
            return null;
        }

        $schedule = $this->permanentSchedule;
        $time = $schedule->time_in && $schedule->time_out
            ? date('g:i A', strtotime((string) $schedule->time_in)).' – '.date('g:i A', strtotime((string) $schedule->time_out))
            : null;

        return [
            'label' => $schedule->hasWeeklyTimes() ? 'Different time per day' : $schedule->resolvedWorkdayType()->shortLabel(),
            'hours' => match (true) {
                $schedule->status === 'rest_day' => 'Rest-day status',
                $schedule->status === 'inactive' => 'Inactive schedule',
                $schedule->shift_name === 'Flexible Shift' => 'Flexible shift',
                $schedule->hasWeeklyTimes() => collect($schedule->weeklyTimes())
                    ->map(fn (array $times, string $day): string => substr($day, 0, 3).' '.date('g:i', strtotime($times['time_in'])).'–'.date('g:i A', strtotime($times['time_out'])))
                    ->take(2)
                    ->implode(', ').(count($schedule->weeklyTimes()) > 2 ? ' …' : ''),
                default => $time,
            },
        ];
    }

    /** @return array{visible: bool, label: string|null} */
    private function rateSummary(Request $request): array
    {
        $visible = (bool) $request->user()?->can('employee-salaries.view')
            && app(PayrollGroupAccessService::class)->allows($this->group_name);

        if (! $visible || ! $this->relationLoaded('activeSalaryProfile')) {
            return ['visible' => false, 'label' => null];
        }

        $salary = $this->activeSalaryProfile;

        return [
            'visible' => true,
            'label' => $salary
                ? '₱'.number_format((float) $salary->basic_salary, 2).($salary->rate_type === 'monthly' ? ' / month' : ' / day')
                : null,
        ];
    }
}
