<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\EmployeeBiometric;
use App\Models\PayrollItem;
use App\Models\PayrollRule;
use App\Repositories\Contracts\Biometrics\EmployeeBiometricRepositoryInterface;
use App\Support\Payroll\PayrollSettingCatalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Payroll Settings → Test computation.
 *
 * Runs the real payroll engine for one employee or a whole payroll group inside a database
 * transaction that is always rolled back, so nothing is saved. A second run with unsaved
 * settings, another saved version or a draft rule shows the difference line by line.
 */
final class PayrollSimulationService
{
    public function __construct(
        private readonly PayrollComputationService $engine,
        private readonly PayrollSettingsService $settings,
        private readonly PayrollRuleService $rules,
        private readonly PayrollEmployeeRosterService $roster,
        private readonly PayrollGroupAccessService $groups,
        private readonly PayrollPeriodService $periods,
        private readonly GovernmentDeductionService $government,
        private readonly EmployeeBiometricRepositoryInterface $employees,
    ) {}

    /**
     * Pickers of the test panel: employees, payroll groups, saved versions and the current cutoff.
     *
     * @return array<string, mixed>
     */
    public function options(): array
    {
        [$month, $year, $type] = $this->periods->getDefaultCutoff();

        return [
            'employees' => $this->employeeOptions(),
            'groups' => collect($this->groups->options())->map(fn (string $label, int|string $value): array => ['value' => (string) $value, 'label' => $label])->values()->all(),
            'versions' => $this->settings->list()->map(fn ($version): array => [
                'value' => (string) $version->id,
                'label' => $version->label.' (from '.$version->effective_from->format('M d, Y').')',
            ])->values()->all(),
            'period' => ['month' => $month, 'year' => $year, 'type' => $type],
        ];
    }

    /** @return list<array{value: string, label: string, hint: string}> payroll-active employees the user may test */
    public function employeeOptions(): array
    {
        return $this->employees->payrollActive()
            ->filter(fn (EmployeeBiometric $employee): bool => $this->groups->allows($employee->group_name))
            ->map(fn (EmployeeBiometric $employee): array => [
                'value' => (string) $employee->id,
                'label' => (string) $employee->effective_name,
                'hint' => trim(($employee->effective_employee_no ?: '').' · '.(EmployeeBiometric::GROUP_LABELS[(int) $employee->group_name] ?? 'No group'), ' ·'),
            ])
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * @param  array{employee_biometric_id?: int|null, garage_group?: string|null, cutoff_month: int, cutoff_year: int, cutoff_type: string, compare_values?: array<string, mixed>|null, compare_version_id?: int|null, compare_rule?: array<string, mixed>|null}  $input
     * @return array<string, mixed>
     */
    public function run(array $input): array
    {
        $month = (int) $input['cutoff_month'];
        $year = (int) $input['cutoff_year'];
        $type = (string) $input['cutoff_type'];
        [$start, $end] = $this->periods->resolveCutoffRange($month, $year, $type);
        $employees = $this->employeesFor($input);

        if ($employees->count() > 1) {
            @set_time_limit(600);
        }

        $baseline = $this->compute($employees, $month, $year, $type, fn (callable $run) => $this->settings->using($start, $run), null);
        $candidate = null;

        $compareRule = $input['compare_rule'] ?? null;
        $compareValues = $input['compare_values'] ?? null;
        $compareVersion = $input['compare_version_id'] ?? null;

        if ($compareValues !== null || $compareVersion !== null || $compareRule !== null) {
            $rules = $compareRule !== null ? $this->rulesWithDraft($compareRule, $start->toDateString(), $end->toDateString()) : null;
            $settings = match (true) {
                $compareValues !== null => fn (callable $run) => $this->settings->usingValues($compareValues, 'Unsaved changes', $run),
                $compareVersion !== null => fn (callable $run) => $this->settings->usingVersion((int) $compareVersion, $run),
                default => fn (callable $run) => $this->settings->using($start, $run),
            };

            $candidate = $this->compute($employees, $month, $year, $type, $settings, $rules);

            if ($compareRule !== null) {
                $candidate['label'] = ($compareValues !== null ? 'Unsaved changes' : $candidate['label']).' + rule "'.($compareRule['name'] ?? 'draft').'"';
            }
        }

        return [
            'period' => [
                'label' => $start->format('M d, Y').' – '.$end->format('M d, Y'),
                'cutoff' => $type === 'second' ? '1st cutoff (26-10)' : '2nd cutoff (11-25)',
                'note' => $type === 'first'
                    ? 'For the 2nd cutoff, the monthly SSS uses the gross of the finalized 1st-cutoff payroll when one exists.'
                    : null,
            ],
            'scope' => $employees->count() === 1 ? 'employee' : 'group',
            'baseline' => $baseline,
            'candidate' => $candidate,
        ];
    }

    /**
     * SSS, PhilHealth and Pag-IBIG for one monthly salary, plus the SSS bracket table.
     *
     * @param  array<string, mixed>|null  $values  unsaved settings, else the version in effect today
     * @return array<string, mixed>
     */
    public function contributions(float $salary, ?array $values): array
    {
        $compute = function () use ($salary): array {
            $result = $this->government->compute([
                'monthly_basic' => $salary,
                'sss_monthly_basic' => $salary,
                'philhealth_monthly_basic' => $salary,
                'pagibig_monthly_basic' => $salary,
            ]);

            return [
                'salary' => round($salary, 2),
                'sss' => [
                    'msc' => (float) $result['sss_msc'],
                    'employee' => (float) $result['sss_employee'],
                    'employer' => (float) $result['sss_employer'],
                    'ec' => (float) $result['sss_ec'],
                    'mpf_msc' => (float) $result['sss_mpf_msc'],
                ],
                'philhealth' => [
                    'base' => (float) $result['philhealth_salary_base'],
                    'rate' => (float) $result['philhealth_premium_rate'],
                    'employee' => (float) $result['philhealth_employee'],
                    'employer' => (float) $result['philhealth_employer'],
                ],
                'pagibig' => [
                    'fund_salary' => (float) $result['pagibig_fund_salary'],
                    'employee_rate' => (float) $result['pagibig_employee_rate'],
                    'employee' => (float) $result['pagibig_employee'],
                    'employer' => (float) $result['pagibig_employer'],
                ],
                'employee_total' => round((float) $result['sss_employee'] + (float) $result['philhealth_employee'] + (float) $result['pagibig_employee'], 2),
                'employer_total' => round((float) $result['sss_employer'] + (float) $result['sss_ec'] + (float) $result['philhealth_employer'] + (float) $result['pagibig_employer'], 2),
                'sss_table' => $this->sssTable(),
            ];
        };

        return $values !== null
            ? $this->settings->usingValues($values, 'Unsaved changes', fn (): array => $compute())
            : $this->settings->using(null, fn (): array => $compute());
    }

    /**
     * @param  Collection<int, EmployeeBiometric>  $employees
     * @param  callable(callable): array<string, mixed>  $withSettings
     * @param  Collection<int, PayrollRule>|null  $rules
     * @return array<string, mixed>
     */
    private function compute(Collection $employees, int $month, int $year, string $type, callable $withSettings, ?Collection $rules): array
    {
        DB::beginTransaction();

        try {
            return $withSettings(function (array $version) use ($employees, $month, $year, $type, $rules): array {
                $items = $this->engine
                    ->simulateItems($employees, $month, $year, $type, $rules)
                    ->map(fn (PayrollItem $item): array => $this->summary($item))
                    ->values();

                return [
                    'label' => $version['label'].($version['effective_from'] ? ' (from '.date('M d, Y', strtotime($version['effective_from'])).')' : ''),
                    'version_id' => $version['id'],
                    'items' => $items->all(),
                    'totals' => [
                        'gross' => round($items->sum('gross'), 2),
                        'net' => round($items->sum('net'), 2),
                        'employee_government' => round($items->sum('employee_government'), 2),
                        'employer_government' => round($items->sum('employer_government'), 2),
                    ],
                ];
            });
        } finally {
            DB::rollBack();
        }
    }

    /**
     * @param  array<string, mixed>  $input
     * @return Collection<int, EmployeeBiometric>
     */
    private function employeesFor(array $input): Collection
    {
        if (! empty($input['employee_biometric_id'])) {
            $employee = $this->employees->find((int) $input['employee_biometric_id']);

            if ($employee === null || ! $this->groups->allows($employee->group_name)) {
                throw ValidationException::withMessages(['employee_biometric_id' => 'Pick an employee from your payroll groups.']);
            }

            return collect([$employee->loadMissing('company')]);
        }

        $group = (string) ($input['garage_group'] ?? '');

        if ($group === '' || ! $this->groups->allows($group)) {
            throw ValidationException::withMessages(['garage_group' => 'Pick one of your payroll groups.']);
        }

        $employees = $this->roster->forGroup($group);

        if ($employees->isEmpty()) {
            throw ValidationException::withMessages(['garage_group' => 'No Active employees with Payroll Inclusion ON were found in this payroll group.']);
        }

        return $employees->values();
    }

    /**
     * Saved rules for the period, with the draft rule added (or replacing the saved one it edits).
     *
     * @param  array<string, mixed>  $data
     * @return Collection<int, PayrollRule>
     */
    private function rulesWithDraft(array $data, string $start, string $end): Collection
    {
        $draftId = isset($data['id']) ? (int) $data['id'] : null;
        $check = ($data['method'] ?? '') === 'formula'
            ? $this->rules->checkFormula((string) ($data['formula'] ?? ''), (string) ($data['kind'] ?? PayrollRule::KIND_EARNING), $draftId)
            : ['ok' => true, 'message' => ''];

        if (! $check['ok']) {
            throw ValidationException::withMessages(['compare_rule.formula' => $check['message']]);
        }

        $draft = $this->rules->draft($data, $draftId);

        return $this->rules->forPeriod($start, $end)
            ->reject(fn (PayrollRule $rule): bool => $draftId !== null && (int) $rule->id === $draftId)
            ->push($draft)
            ->sortBy(fn (PayrollRule $rule): string => sprintf('%d-%08d-%010d', $rule->isEarning() ? 0 : 1, (int) $rule->sort_order, (int) ($rule->id ?? 9999999999)))
            ->values();
    }

    /** @return array<string, mixed> one employee's result, as lines that can be compared */
    private function summary(PayrollItem $item): array
    {
        $meta = (array) ($item->meta ?? []);
        $lossDeducted = (bool) data_get($meta, 'attendance_deductions_are_deducted_from_monthly_base', false);
        $lines = [];
        $line = function (string $key, string $label, string $section, float $amount, ?string $hint = null, bool $counted = true) use (&$lines): void {
            $lines[] = ['key' => $key, 'label' => $label, 'section' => $section, 'amount' => round($amount, 2), 'hint' => $hint, 'counted' => $counted];
        };

        $isMonthly = data_get($meta, 'pay_architecture.money_model') === 'monthly_salary_divided_by_2_less_attendance_loss';
        $line('base', 'Base pay', 'earnings', (float) $item->regular_pay, $isMonthly
            ? 'Monthly ₱'.number_format((float) $item->monthly_rate, 2).' ÷ '.(float) config('payroll.salary_rate.monthly_cutoff_divisor', 2)
            : number_format((float) data_get($meta, 'pay_architecture.regular_payable_hours_for_audit', 0), 2).' hr × ₱'.number_format((float) $item->hourly_rate, 2));

        $lossHint = $lossDeducted ? null : 'Not deducted: already left out of payable hours';
        $line('late', 'Late', 'attendance', (float) $item->late_deduction, ((int) $item->total_late_minutes).' min'.($lossHint ? ' · '.$lossHint : ''), $lossDeducted);
        $line('undertime', 'Undertime', 'attendance', (float) $item->undertime_deduction, ((int) $item->total_undertime_minutes).' min'.($lossHint ? ' · '.$lossHint : ''), $lossDeducted);
        $line('absence', 'Absence / unpaid rest day', 'attendance', (float) $item->absence_deduction, ((float) $item->total_absent_days + 0).' day(s)'.($lossHint ? ' · '.$lossHint : ''), $lossDeducted);

        $line('holiday', 'Holiday pay', 'earnings', (float) $item->holiday_pay, ((int) $item->total_holiday_worked).' worked');
        $line('rest_day', 'Rest day pay', 'earnings', (float) $item->rest_day_pay, ((int) $item->total_rest_day_worked).' worked');
        $line('leave', 'Leave pay', 'earnings', (float) $item->leave_pay);
        $line('overtime', 'Overtime', 'earnings', (float) $item->overtime_pay, number_format(((int) $item->total_overtime_minutes) / 60, 2).' hr');
        $line('night_diff', 'Night differential', 'earnings', (float) $item->night_differential_pay, number_format(((int) $item->total_night_differential_minutes) / 60, 2).' hr');
        $line('allowance', 'Allowance', 'earnings', (float) data_get($meta, 'allowance.allowance_per_cutoff', 0));
        $line('adjustment_add', 'Salary adjustment', 'earnings', (float) data_get($meta, 'manual_adjustments.additions', 0));

        foreach ((array) data_get($meta, 'custom_rules.earnings', []) as $rule) {
            $line('rule:'.$rule['code'], (string) $rule['name'], 'earnings', (float) $rule['amount'], $rule['error'] ? 'Error: '.$rule['error'] : (string) $rule['description']);
        }

        $line('gross', 'Gross pay', 'total', (float) $item->gross_pay);

        $government = (array) data_get($meta, 'government_after_profile_schedule', []);
        $line('sss', 'SSS', 'deductions', (float) $item->sss_employee, 'MSC ₱'.number_format((float) data_get($government, 'sss_msc', data_get($meta, 'government_raw_before_schedule.sss_msc', 0)), 2));
        $line('philhealth', 'PhilHealth', 'deductions', (float) $item->philhealth_employee, 'Base ₱'.number_format((float) data_get($meta, 'government_raw_before_schedule.philhealth_salary_base', 0), 2).' × '.round((float) data_get($meta, 'government_raw_before_schedule.philhealth_premium_rate', 0) * 100, 2).'%');
        $line('pagibig', 'Pag-IBIG', 'deductions', (float) $item->pagibig_employee, 'Fund salary ₱'.number_format((float) data_get($meta, 'government_raw_before_schedule.pagibig_fund_salary', 0), 2));

        foreach ((array) data_get($meta, 'salary_deductions', []) as $index => $loan) {
            $line('loan:'.mb_strtolower((string) data_get($loan, 'name', $index)), (string) data_get($loan, 'name', 'Deduction'), 'deductions', (float) data_get($loan, 'amount', 0));
        }

        $line('adjustment_ded', 'Salary adjustment', 'deductions', (float) data_get($meta, 'manual_adjustments.deductions', 0));

        foreach ((array) data_get($meta, 'custom_rules.deductions', []) as $rule) {
            $line('rule:'.$rule['code'], (string) $rule['name'], 'deductions', (float) $rule['amount'], $rule['error'] ? 'Error: '.$rule['error'] : (string) $rule['description']);
        }

        $line('net', 'Net pay', 'total', (float) $item->net_pay);

        $line('sss_er', 'SSS (employer)', 'employer', (float) $item->sss_employer);
        $line('ec', 'SSS EC (employer)', 'employer', (float) $item->sss_ec);
        $line('philhealth_er', 'PhilHealth (employer)', 'employer', (float) $item->philhealth_employer);
        $line('pagibig_er', 'Pag-IBIG (employer)', 'employer', (float) $item->pagibig_employer);

        $notes = [];
        if (data_get($meta, 'safe_zero_pay')) {
            $notes[] = (string) data_get($meta, 'safe_zero_pay_reason');
        }
        if ((int) data_get($meta, 'attendance_summary_coverage.missing_days', 0) > 0) {
            $notes[] = data_get($meta, 'attendance_summary_coverage.missing_days').' day(s) of this cutoff have no Attendance Summary yet.';
        }
        if (! data_get($meta, 'salary_profile_found', true) || ($item->payroll_employee_salary_id === null && ! data_get($meta, 'safe_zero_pay'))) {
            $notes[] = 'No active Employee Rate found, so all rates are 0.';
        }

        return [
            'employee_biometric_id' => (int) $item->employee_biometric_id,
            'name' => (string) $item->employee_name,
            'employee_no' => $item->employee_no,
            'rate_type' => (string) $item->rate_type,
            'facts' => [
                ['label' => 'Rate type', 'value' => ucfirst((string) ($item->rate_type ?: '—'))],
                ['label' => 'Monthly rate', 'value' => '₱'.number_format((float) $item->monthly_rate, 2)],
                ['label' => 'Daily rate', 'value' => '₱'.number_format((float) $item->daily_rate, 2)],
                ['label' => 'Hourly rate', 'value' => '₱'.number_format((float) $item->hourly_rate, 2)],
                ['label' => 'Days worked', 'value' => number_format((float) $item->total_worked_days, 2)],
                ['label' => 'Payable hours', 'value' => number_format((float) $item->total_payable_hours, 2)],
                ['label' => 'Late / undertime', 'value' => ((int) $item->total_late_minutes).' / '.((int) $item->total_undertime_minutes).' min'],
                ['label' => 'Absent days', 'value' => number_format((float) $item->total_absent_days, 0)],
            ],
            'lines' => $lines,
            'gross' => round((float) $item->gross_pay, 2),
            'net' => round((float) $item->net_pay, 2),
            'employee_government' => round((float) $item->total_employee_government_deductions, 2),
            'employer_government' => round((float) $item->total_employer_government_contributions, 2),
            'notes' => $notes,
        ];
    }

    /** @return list<array{from: float, to: float|null, msc: float, employee: float, employer: float, ec: float}> */
    private function sssTable(): array
    {
        $rules = (array) config('sss.business_employee', []);
        $minimum = (float) ($rules['minimum_msc'] ?? 0);
        $maximum = (float) ($rules['maximum_msc'] ?? 0);
        $step = max(1.0, (float) ($rules['msc_increment'] ?? 500));
        $rows = [];

        for ($msc = $minimum, $guard = 0; $msc <= $maximum + 0.001 && $guard < 400; $msc += $step, $guard++) {
            $result = $this->government->compute(['monthly_basic' => $msc, 'sss_monthly_basic' => $msc, 'philhealth_monthly_basic' => 0, 'pagibig_monthly_basic' => 0]);
            $rows[] = [
                'from' => (float) ($result['sss_compensation_range_minimum'] ?? 0),
                'to' => isset($result['sss_compensation_range_maximum']) ? (float) $result['sss_compensation_range_maximum'] : null,
                'msc' => (float) $result['sss_msc'],
                'employee' => (float) $result['sss_employee'],
                'employer' => (float) $result['sss_employer'],
                'ec' => (float) $result['sss_ec'],
            ];
        }

        return $rows;
    }

    /** @return array<string, mixed> catalog defaults, for the form's "reset" button */
    public function defaults(): array
    {
        return PayrollSettingCatalog::defaults();
    }
}
