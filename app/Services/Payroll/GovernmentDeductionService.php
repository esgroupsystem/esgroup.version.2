<?php

namespace App\Services\Payroll;

class GovernmentDeductionService
{
    public function __construct(
        private readonly SssContributionService $sssContributionService
    ) {}

    private const PHILHEALTH_PREMIUM_RATE = 0.05;

    private const PHILHEALTH_EMPLOYEE_SHARE = 0.50;

    private const PHILHEALTH_EMPLOYER_SHARE = 0.50;

    private const PHILHEALTH_INCOME_FLOOR = 10000.00;

    private const PHILHEALTH_INCOME_CEILING = 100000.00;

    private const PAGIBIG_LOW_EMPLOYEE_RATE = 0.01;

    private const PAGIBIG_REGULAR_EMPLOYEE_RATE = 0.02;

    private const PAGIBIG_EMPLOYER_RATE = 0.02;

    private const PAGIBIG_LOW_SALARY_THRESHOLD = 1500.00;

    private const PAGIBIG_MAXIMUM_FUND_SALARY = 10000.00;

    public function compute(array $data): array
    {
        $monthlyBasic = $this->money($data['monthly_basic'] ?? 0);
        $sssMonthlyBasic = $this->money($data['sss_monthly_basic'] ?? $monthlyBasic);
        $philHealthMonthlyBasic = $this->money($data['philhealth_monthly_basic'] ?? $monthlyBasic);
        $pagibigMonthlyBasic = $this->money($data['pagibig_monthly_basic'] ?? $monthlyBasic);

        $sss = $this->computeSss($sssMonthlyBasic);
        $philHealth = $this->computePhilHealth($philHealthMonthlyBasic);
        $pagibig = $this->computePagIbig($pagibigMonthlyBasic);

        return $this->total([
            'sss_employee' => $sss['employee'],
            'sss_employer' => $sss['employer'],
            'sss_ec' => $sss['ec'],
            'sss_employee_regular_ss' => $sss['employee_regular_ss'],
            'sss_employee_mpf' => $sss['employee_mpf'],
            'sss_employer_regular_ss' => $sss['employer_regular_ss'],
            'sss_employer_mpf' => $sss['employer_mpf'],
            'sss_employer_total_with_ec' => $sss['employer_total_with_ec'],
            'sss_total_contribution' => $sss['total_contribution'],
            'sss_compensation_range_minimum' => $sss['range_minimum'],
            'sss_compensation_range_maximum' => $sss['range_maximum'],
            'sss_circular_number' => $sss['circular_number'],
            'sss_effective_from' => $sss['effective_from'],
            'philhealth_employee' => $philHealth['employee'],
            'philhealth_employer' => $philHealth['employer'],
            'pagibig_employee' => $pagibig['employee'],
            'pagibig_employer' => $pagibig['employer'],
            'withholding_tax' => 0.00,
            'taxable_cutoff_compensation' => $this->money(
                $data['taxable_cutoff_compensation'] ?? 0
            ),
            'sss_basis' => $sssMonthlyBasic,
            'sss_msc' => $sss['msc'],
            'philhealth_basis' => $philHealthMonthlyBasic,
            'philhealth_salary_base' => $philHealth['salary_base'],
            'pagibig_basis' => $pagibigMonthlyBasic,
            'pagibig_fund_salary' => $pagibig['fund_salary'],
        ]);
    }

    public function applyDeductionSchedule(
        array $monthlyGovernment,
        string $cutoffType,
        array $profileSchedules = [],
        ?float $taxableCutoffCompensation = null
    ): array {
        $scheduleConfig = config('payroll.government_deduction_schedule', []);

        $schedules = [
            'sss' => $profileSchedules['sss']
                ?? $scheduleConfig['sss']
                ?? 'first_cutoff',

            'philhealth' => $profileSchedules['philhealth']
                ?? $scheduleConfig['philhealth']
                ?? 'first_cutoff',

            'pagibig' => $profileSchedules['pagibig']
                ?? $scheduleConfig['pagibig']
                ?? 'first_cutoff',
        ];

        $government = [
            'sss_employee' => $this->scheduledMonthlyContribution(
                (float) ($monthlyGovernment['sss_employee'] ?? 0),
                $schedules['sss'],
                $cutoffType
            ),
            'sss_employer' => $this->scheduledMonthlyContribution(
                (float) ($monthlyGovernment['sss_employer'] ?? 0),
                $schedules['sss'],
                $cutoffType
            ),
            'sss_ec' => $this->scheduledMonthlyContribution(
                (float) ($monthlyGovernment['sss_ec'] ?? 0),
                $schedules['sss'],
                $cutoffType
            ),

            'philhealth_employee' => $this->scheduledMonthlyContribution(
                (float) ($monthlyGovernment['philhealth_employee'] ?? 0),
                $schedules['philhealth'],
                $cutoffType
            ),
            'philhealth_employer' => $this->scheduledMonthlyContribution(
                (float) ($monthlyGovernment['philhealth_employer'] ?? 0),
                $schedules['philhealth'],
                $cutoffType
            ),

            'pagibig_employee' => $this->scheduledMonthlyContribution(
                (float) ($monthlyGovernment['pagibig_employee'] ?? 0),
                $schedules['pagibig'],
                $cutoffType
            ),
            'pagibig_employer' => $this->scheduledMonthlyContribution(
                (float) ($monthlyGovernment['pagibig_employer'] ?? 0),
                $schedules['pagibig'],
                $cutoffType
            ),

            'withholding_tax' => 0.00,
        ];

        $taxableCompensation = $this->money(
            $taxableCutoffCompensation
                ?? $monthlyGovernment['taxable_cutoff_compensation']
                ?? 0
        );

        $government['schedule_meta'] = [
            'sss' => [
                'schedule' => $this->normalizeSchedule($schedules['sss']),
                'monthly_employee_share' => $this->money(
                    $monthlyGovernment['sss_employee'] ?? 0
                ),
                'monthly_employer_share' => $this->money(
                    $monthlyGovernment['sss_employer'] ?? 0
                ),
                'monthly_ec_share' => $this->money(
                    $monthlyGovernment['sss_ec'] ?? 0
                ),
                'monthly_employee_regular_ss' => $this->money(
                    $monthlyGovernment['sss_employee_regular_ss'] ?? 0
                ),
                'monthly_employee_mpf' => $this->money(
                    $monthlyGovernment['sss_employee_mpf'] ?? 0
                ),
                'monthly_employer_regular_ss' => $this->money(
                    $monthlyGovernment['sss_employer_regular_ss'] ?? 0
                ),
                'monthly_employer_mpf' => $this->money(
                    $monthlyGovernment['sss_employer_mpf'] ?? 0
                ),
                'monthly_total_contribution' => $this->money(
                    $monthlyGovernment['sss_total_contribution'] ?? 0
                ),
                'monthly_salary_credit' => $this->money(
                    $monthlyGovernment['sss_msc'] ?? 0
                ),
                'compensation_range_minimum' => $monthlyGovernment['sss_compensation_range_minimum'] ?? null,
                'compensation_range_maximum' => $monthlyGovernment['sss_compensation_range_maximum'] ?? null,
                'circular_number' => $monthlyGovernment['sss_circular_number'] ?? '2024-006',
                'effective_from' => $monthlyGovernment['sss_effective_from'] ?? '2025-01-01',
                'deducted_employee_share' => $government['sss_employee'],
                'scheduled_employer_share' => $government['sss_employer'],
                'scheduled_ec_share' => $government['sss_ec'],
            ],
            'philhealth' => [
                'schedule' => $this->normalizeSchedule($schedules['philhealth']),
                'monthly_employee_share' => $this->money(
                    $monthlyGovernment['philhealth_employee'] ?? 0
                ),
                'deducted_employee_share' => $government['philhealth_employee'],
            ],
            'pagibig' => [
                'schedule' => $this->normalizeSchedule($schedules['pagibig']),
                'monthly_employee_share' => $this->money(
                    $monthlyGovernment['pagibig_employee'] ?? 0
                ),
                'deducted_employee_share' => $government['pagibig_employee'],
            ],
            'withholding_tax' => [
                'schedule' => 'none',
                'taxable_before_government' => $taxableCompensation,
                'deducted_tax' => 0.00,
                'remarks' => 'Disabled by company policy. Tax is not deducted in payroll.',
            ],
        ];

        foreach ([
            'sss_basis',
            'sss_msc',
            'philhealth_basis',
            'philhealth_salary_base',
            'pagibig_basis',
            'pagibig_fund_salary',
        ] as $key) {
            if (array_key_exists($key, $monthlyGovernment)) {
                $government[$key] = $monthlyGovernment[$key];
            }
        }

        return $this->total($government);
    }

    protected function scheduledMonthlyContribution(
        float $monthlyAmount,
        ?string $schedule,
        string $cutoffType
    ): float {
        $schedule = $this->normalizeSchedule($schedule);

        return match ($schedule) {
            'none' => 0.00,
            'first' => $cutoffType === 'first' ? $this->money($monthlyAmount) : 0.00,
            'second' => $cutoffType === 'second' ? $this->money($monthlyAmount) : 0.00,
            'every' => $this->money($monthlyAmount / 2),
            default => 0.00,
        };
    }

    protected function normalizeSchedule(?string $schedule): string
    {
        $schedule = strtolower(trim((string) $schedule));
        $schedule = str_replace([' ', '-', '/'], '_', $schedule);
        $schedule = preg_replace('/_+/', '_', $schedule);

        return match ($schedule) {
            'none',
            'no',
            'no_deduction',
            'not_applicable',
            'n_a',
            'na' => 'none',

            'first',
            '1st',
            'first_cutoff',
            '1st_cutoff',
            'first_cutoff_only',
            '1st_cutoff_only' => 'first',

            'second',
            '2nd',
            'second_cutoff',
            '2nd_cutoff',
            'second_cutoff_only',
            '2nd_cutoff_only' => 'second',

            'every',
            'every_cutoff',
            'per_cutoff',
            'each_cutoff',
            'both',
            'monthly',
            'all' => 'every',

            default => 'every',
        };
    }

    protected function total(array $government): array
    {
        foreach ([
            'sss_employee',
            'sss_employer',
            'sss_ec',
            'philhealth_employee',
            'philhealth_employer',
            'pagibig_employee',
            'pagibig_employer',
        ] as $key) {
            $government[$key] = $this->money($government[$key] ?? 0);
        }

        $government['withholding_tax'] = 0.00;

        $government['total_employee_government_deductions'] = $this->money(
            $government['sss_employee']
            + $government['philhealth_employee']
            + $government['pagibig_employee']
        );

        $government['total_employer_government_contributions'] = $this->money(
            $government['sss_employer']
            + $government['sss_ec']
            + $government['philhealth_employer']
            + $government['pagibig_employer']
        );

        return $government;
    }

    protected function computeSss(float $monthlyBasic): array
    {
        return $this->sssContributionService->compute($monthlyBasic);
    }

    protected function computePhilHealth(float $monthlyBasic): array
    {
        $monthlyBasic = $this->money($monthlyBasic);

        if ($monthlyBasic <= 0) {
            return [
                'employee' => 0.00,
                'employer' => 0.00,
                'salary_base' => 0.00,
            ];
        }

        $salaryBase = min(
            max($monthlyBasic, self::PHILHEALTH_INCOME_FLOOR),
            self::PHILHEALTH_INCOME_CEILING
        );

        $monthlyPremium = $salaryBase * self::PHILHEALTH_PREMIUM_RATE;

        return [
            'employee' => $this->money(
                $monthlyPremium * self::PHILHEALTH_EMPLOYEE_SHARE
            ),
            'employer' => $this->money(
                $monthlyPremium * self::PHILHEALTH_EMPLOYER_SHARE
            ),
            'salary_base' => $this->money($salaryBase),
        ];
    }

    protected function computePagIbig(float $monthlyBasic): array
    {
        $monthlyBasic = $this->money($monthlyBasic);

        if ($monthlyBasic <= 0) {
            return [
                'employee' => 0.00,
                'employer' => 0.00,
                'fund_salary' => 0.00,
            ];
        }

        $fundSalary = min($monthlyBasic, self::PAGIBIG_MAXIMUM_FUND_SALARY);
        $employeeRate = $monthlyBasic <= self::PAGIBIG_LOW_SALARY_THRESHOLD
            ? self::PAGIBIG_LOW_EMPLOYEE_RATE
            : self::PAGIBIG_REGULAR_EMPLOYEE_RATE;

        return [
            'employee' => $this->money($fundSalary * $employeeRate),
            'employer' => $this->money($fundSalary * self::PAGIBIG_EMPLOYER_RATE),
            'fund_salary' => $this->money($fundSalary),
        ];
    }

    private function money(mixed $value): float
    {
        return round(max(0, (float) $value), 2);
    }
}
