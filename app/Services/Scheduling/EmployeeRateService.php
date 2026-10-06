<?php

declare(strict_types=1);

namespace App\Services\Scheduling;

use App\Models\EmployeeBiometric;
use App\Models\PayrollEmployeeSalary;
use App\Repositories\Contracts\Biometrics\EmployeeBiometricRepositoryInterface;
use App\Repositories\Contracts\Scheduling\EmployeeSalaryRepositoryInterface;
use App\Repositories\Contracts\Scheduling\PlottingScheduleRepositoryInterface;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use App\Services\Payroll\PayrollDeductionService;
use App\Support\PayrollEmployeeNameFormatter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Scheduling & Rates → Employee Rates: each person's pay basis, allowances, government
 * contribution schedule, loans and other deductions. Hourly / per-minute / daily rates are
 * computed from the salary and the person's paid hours per day (Work Schedule).
 */
final class EmployeeRateService
{
    private const SYNC_LOCK = 'payroll:employee-salaries:sync-from-biometrics';

    public function __construct(
        private readonly EmployeeSalaryRepositoryInterface $salaries,
        private readonly EmployeeBiometricRepositoryInterface $biometrics,
        private readonly PlottingScheduleRepositoryInterface $schedules,
        private readonly EmployeeBiometricIdentityService $identity,
        private readonly PayrollDeductionService $deductions,
    ) {}

    /**
     * Rates with a `payroll_preview` attribute (monthly government shares etc.).
     *
     * @param  string|list<int|string>|null  $allowedGroups  the user's payroll groups
     * @return LengthAwarePaginator<int, PayrollEmployeeSalary>
     */
    public function paginate(string $search, string $group, string $employmentStatus, string|array|null $allowedGroups): LengthAwarePaginator
    {
        $page = $this->salaries->paginateDirectory($search, $group, $employmentStatus, $allowedGroups);
        foreach ($page->items() as $salary) {
            $salary->setAttribute('payroll_preview', $this->deductions->salaryPreview($salary));
        }

        return $page;
    }

    /** @return Collection<int, string> */
    public function groups(): Collection
    {
        return $this->biometrics->groups();
    }

    /**
     * People the form can pick, with identity and paid hours from their permanent schedule.
     *
     * @param  string|list<int|string>|null  $allowedGroups
     * @return Collection<int, array<string, mixed>>
     */
    public function people(string|array|null $allowedGroups): Collection
    {
        return $this->biometrics->payrollActiveWithSchedule($allowedGroups)
            ->map(fn (EmployeeBiometric $employee): array => $this->person($employee))
            ->values();
    }

    /**
     * One entry of people() for a single employee (the employee profile).
     *
     * @return array<string, mixed>
     */
    public function person(EmployeeBiometric $employee): array
    {
        $snapshot = $this->identity->snapshot($employee);
        $schedule = $employee->permanentSchedule;

        return [
            'employee_biometric_id' => (int) $employee->id,
            'biometric_employee_id' => $snapshot['biometric_employee_id'],
            'employee_no' => $snapshot['employee_no'],
            'employee_name' => $snapshot['employee_name'],
            'display_name' => PayrollEmployeeNameFormatter::display($snapshot['employee_name']),
            'crosschex_id' => $snapshot['crosschex_id'],
            'group_name' => $employee->group_name !== null ? (string) $employee->group_name : null,
            'paid_work_hours' => (float) ($schedule?->paidWorkHours() ?? 8.0),
            'workday_label' => $schedule?->resolvedWorkdayType()->shortLabel() ?? '8 hrs + 1 hr lunch',
        ];
    }

    /** The rate the employee profile shows and edits (active first, newest), or null. */
    public function forEmployee(EmployeeBiometric $employee): ?PayrollEmployeeSalary
    {
        return $this->salaries->forEmployee((int) $employee->id);
    }

    /** Rate with its other deductions and the person's permanent schedule, for the edit form. */
    public function forEdit(PayrollEmployeeSalary $salary): PayrollEmployeeSalary
    {
        return $this->salaries->load($salary, ['otherDeductions', 'employeeBiometric.permanentSchedule']);
    }

    /**
     * @param  array<string, mixed>  $data  validated form
     */
    public function create(array $data): PayrollEmployeeSalary
    {
        $employee = $this->biometrics->findPayrollActiveOrFail((int) $data['employee_biometric_id']);
        $payload = $this->payload($data, $employee);

        return DB::transaction(function () use ($payload, $data): PayrollEmployeeSalary {
            $salary = $this->salaries->create($payload);
            $this->salaries->replaceOtherDeductions($salary, $this->otherDeductionRows($data['other_deductions'] ?? []));

            return $salary;
        });
    }

    /** @param array<string, mixed> $data validated form */
    public function update(PayrollEmployeeSalary $salary, array $data): void
    {
        $employee = $this->biometrics->findOrFail((int) ($data['employee_biometric_id'] ?? $salary->employee_biometric_id));
        $payload = $this->payload($data, $employee);

        DB::transaction(function () use ($salary, $payload, $data): void {
            $this->salaries->update($salary, $payload);
            $this->salaries->replaceOtherDeductions($salary, $this->otherDeductionRows($data['other_deductions'] ?? []));
        });
    }

    public function delete(PayrollEmployeeSalary $salary): void
    {
        $this->salaries->delete($salary);
    }

    /**
     * Adds a blank rate for every payroll-active person without one and refreshes the identity
     * fields of the others. One run at a time.
     *
     * @return array{inserted: int, updated: int, skipped: int}|null null when another sync is already running
     */
    public function syncFromBiometrics(): ?array
    {
        $lock = Cache::lock(self::SYNC_LOCK, 300);
        if (! $lock->get()) {
            return null;
        }

        $totals = ['inserted' => 0, 'updated' => 0, 'skipped' => 0];

        try {
            $this->biometrics->eachPayrollActiveChunk(200, function (Collection $people) use (&$totals): void {
                $chunk = DB::transaction(fn (): array => $this->syncPeople($people), 3);
                foreach ($chunk as $key => $count) {
                    $totals[$key] += $count;
                }
            });
        } finally {
            $lock->release();
        }

        return $totals;
    }

    /**
     * @param  Collection<int, EmployeeBiometric>  $people
     * @return array{inserted: int, updated: int, skipped: int}
     */
    private function syncPeople(Collection $people): array
    {
        $counts = ['inserted' => 0, 'updated' => 0, 'skipped' => 0];

        foreach ($people as $employee) {
            $snapshot = $this->identity->snapshot($employee);
            $name = $this->clean($snapshot['employee_name'] ?? null);
            if ($name === null || strcasecmp($name, 'Unknown Employee') === 0) {
                $counts['skipped']++;

                continue;
            }

            // employee_biometric_id is the canonical local identity; biometric_employee_id is only
            // a legacy/source value and may repeat across different CrossChex accounts.
            $salary = $this->salaries->latestForEmployeeForUpdate($employee->id);
            if ($salary === null) {
                $this->salaries->create($this->blankPayload($employee, $snapshot));
                $counts['inserted']++;

                continue;
            }

            $this->salaries->update($salary, [
                'biometric_employee_id' => $this->clean($snapshot['biometric_employee_id'] ?? null) ?: $salary->biometric_employee_id,
                'employee_no' => $this->clean($snapshot['employee_no'] ?? null) ?: $salary->employee_no,
                'employee_name' => $name,
                'crosschex_id' => $this->clean($snapshot['crosschex_id'] ?? null) ?: $salary->crosschex_id,
                'is_active' => (bool) $employee->is_payroll_active,
            ]);
            $counts['updated']++;
        }

        return $counts;
    }

    /** Paid hours per day from the permanent schedule, else the configured default. */
    private function paidHours(int $employeeBiometricId): float
    {
        return $this->schedules->permanentFor($employeeBiometricId)?->paidWorkHours()
            ?? max(1, (float) config('payroll.salary_rate.paid_hours_per_day', 8));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(array $data, EmployeeBiometric $employee): array
    {
        $snapshot = $this->identity->snapshot($employee);
        $rates = $this->deductions->computeRates((float) $data['basic_salary'], $data['rate_type'], $this->paidHours($employee->id));

        $payload = [
            'employee_biometric_id' => $employee->id,
            'employee_no' => $snapshot['employee_no'],
            'employee_name' => $this->clean($data['employee_name'] ?? null) ?: $snapshot['employee_name'],
            'crosschex_id' => $snapshot['crosschex_id'],
            'biometric_employee_id' => $snapshot['biometric_employee_id'],
            'rate_type' => $data['rate_type'],
            'basic_salary' => $data['basic_salary'],
            'allowance' => $data['allowance'] ?? 0,
            'allowance_release_schedule' => $data['allowance_release_schedule'],
            'sim_load_allowance' => $data['sim_load_allowance'] ?? 0,
            'sim_load_release_schedule' => $data['sim_load_release_schedule'],
            'paid_night_differential' => (bool) ($data['paid_night_differential'] ?? false),
            'paid_day_off' => (bool) ($data['paid_day_off'] ?? true),
            'sss_contribution_cutoff' => $data['sss_contribution_cutoff'],
            'pagibig_contribution_cutoff' => $data['pagibig_contribution_cutoff'],
            'philhealth_contribution_cutoff' => $data['philhealth_contribution_cutoff'],
            'ot_rate_per_hour' => $rates['hourly_rate'],
            'late_deduction_per_minute' => $rates['per_minute_rate'],
            'undertime_deduction_per_minute' => $rates['per_minute_rate'],
            'absent_deduction_per_day' => $rates['daily_rate'],
        ];

        foreach (PayrollEmployeeSalary::LOAN_PREFIXES as $prefix) {
            $payload["{$prefix}_total_amount"] = $data["{$prefix}_total_amount"] ?? 0;
            $payload["{$prefix}_payment_amount"] = $data["{$prefix}_payment_amount"] ?? 0;
            $payload["{$prefix}_deduction_schedule"] = $data["{$prefix}_deduction_schedule"];
            $payload["{$prefix}_start_date"] = $data["{$prefix}_start_date"] ?? null;
        }

        // Legacy per-cutoff columns still read by older payroll code.
        $payload['sss_loan'] = $data['sss_loan_payment_amount'] ?? 0;
        $payload['pagibig_loan'] = $data['pagibig_loan_payment_amount'] ?? 0;
        $payload['vale'] = $data['cash_advance_payment_amount'] ?? 0;
        $payload['other_loans'] = round(
            (float) ($data['other_loan_payment_amount'] ?? 0)
            + collect($data['other_deductions'] ?? [])->sum(fn (array $row): float => (float) ($row['payment_amount'] ?? 0)),
            2,
        );
        $payload['is_active'] = (bool) ($data['is_active'] ?? true);
        $payload['remarks'] = $data['remarks'] ?? null;

        return $payload;
    }

    /**
     * A new person's rate: daily, zero salary, standard schedules, no loans.
     *
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    private function blankPayload(EmployeeBiometric $employee, array $snapshot): array
    {
        $payload = [
            'employee_biometric_id' => $employee->id,
            'biometric_employee_id' => $snapshot['biometric_employee_id'],
            'employee_no' => $snapshot['employee_no'],
            'employee_name' => $snapshot['employee_name'],
            'crosschex_id' => $snapshot['crosschex_id'],
            'rate_type' => 'daily',
            'basic_salary' => 0,
            'allowance' => 0,
            'allowance_release_schedule' => 'every_cutoff',
            'sim_load_allowance' => 0,
            'sim_load_release_schedule' => 'every_cutoff',
            'paid_night_differential' => false,
            'paid_day_off' => true,
            'sss_contribution_cutoff' => 'first_cutoff',
            'pagibig_contribution_cutoff' => 'second_cutoff',
            'philhealth_contribution_cutoff' => 'second_cutoff',
            'ot_rate_per_hour' => 0,
            'late_deduction_per_minute' => 0,
            'undertime_deduction_per_minute' => 0,
            'absent_deduction_per_day' => 0,
            'sss_loan' => 0,
            'pagibig_loan' => 0,
            'vale' => 0,
            'other_loans' => 0,
            'is_active' => true,
            'remarks' => null,
        ];

        foreach (PayrollEmployeeSalary::LOAN_PREFIXES as $prefix) {
            $payload["{$prefix}_total_amount"] = 0;
            $payload["{$prefix}_payment_amount"] = 0;
            $payload["{$prefix}_deduction_schedule"] = 'none';
            $payload["{$prefix}_start_date"] = null;
        }

        return $payload;
    }

    /**
     * Keeps rows that say something; an amount without a name becomes "Other Deduction".
     *
     * @param  array<int, array<string, mixed>>  $deductions
     * @return list<array<string, mixed>>
     */
    private function otherDeductionRows(array $deductions): array
    {
        return collect($deductions)
            ->map(function (array $deduction): array {
                $name = $this->clean($deduction['name'] ?? null);
                $total = (float) ($deduction['total_amount'] ?? 0);
                $payment = (float) ($deduction['payment_amount'] ?? 0);

                return [
                    'name' => $name ?? ($total > 0 || $payment > 0 ? 'Other Deduction' : null),
                    'total_amount' => round($total, 2),
                    'payment_amount' => round($payment, 2),
                    'deduction_schedule' => ($deduction['deduction_schedule'] ?? 'none') ?: 'none',
                    'start_date' => $deduction['start_date'] ?? null,
                    'remarks' => $this->clean($deduction['remarks'] ?? null),
                    'is_active' => true,
                ];
            })
            ->filter(fn (array $row): bool => ! empty($row['name'])
                && ($row['total_amount'] > 0 || $row['payment_amount'] > 0 || $row['deduction_schedule'] !== 'none' || ! empty($row['start_date']) || ! empty($row['remarks'])))
            ->values()
            ->all();
    }

    private function clean(mixed $value): ?string
    {
        $cleaned = trim((string) $value);

        return $cleaned === '' ? null : $cleaned;
    }
}
