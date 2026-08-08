<?php

namespace App\Services\Payroll;

use App\Models\BenefitContributionRecord;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use RuntimeException;

class BenefitContributionPostingService
{
    public function __construct(
        private readonly MonthlyGovernmentContributionService $monthlyContributionService
    ) {}

    /**
     * Post one exact MONTHLY Benefits Record per employee.
     *
     * The record is created only when the business 2nd cutoff (11-25, legacy
     * key `first`) is finalized because only then are both halves of the monthly
     * contribution cycle locked:
     *
     *  - business 1st cutoff: 26th previous month to 10th
     *  - business 2nd cutoff: 11th to 25th
     *
     * This intentionally replaces the old per-cutoff Benefits Record behavior,
     * which could display only PHP 450 SSS or omit MPF because it stored the
     * amount scheduled to one cutoff rather than the full monthly liability.
     */
    public function postForPayroll(
        Payroll $payroll,
        ?int $userId = null,
        ?CarbonInterface $postedAt = null
    ): int {
        if ($payroll->status !== 'finalized') {
            throw new RuntimeException('Benefits Records can only be posted from a finalized payroll.');
        }

        // Business 1st cutoff is not a complete monthly contribution cycle yet.
        if ((string) $payroll->cutoff_type !== 'first') {
            return 0;
        }

        $postedAt ??= now('Asia/Manila');

        $openingPayroll = Payroll::query()
            ->where('contribution_month', (int) $payroll->contribution_month)
            ->where('contribution_year', (int) $payroll->contribution_year)
            ->where('garage_group', (string) $payroll->garage_group)
            ->where('cutoff_type', 'second')
            ->where('status', 'finalized')
            ->latest('id')
            ->first();

        if (! $openingPayroll) {
            throw new RuntimeException(sprintf(
                'Cannot post Benefits Records for %s because the finalized business 1st cutoff (26-10) was not found.',
                $payroll->contribution_label
            ));
        }

        $relations = [
            'items.employee.asset',
            'items.employeeBiometric.company',
            'items.employeeBiometric.activeSalaryProfile.employee.asset',
        ];

        $openingPayroll->loadMissing($relations);
        $payroll->loadMissing($relations);

        $openingItems = $this->keyItems($openingPayroll->items);
        $closingItems = $this->keyItems($payroll->items);
        $employeeKeys = $openingItems->keys()->merge($closingItems->keys())->unique()->values();

        $count = 0;

        foreach ($employeeKeys as $employeeKey) {
            /** @var PayrollItem|null $openingItem */
            $openingItem = $openingItems->get($employeeKey);
            /** @var PayrollItem|null $closingItem */
            $closingItem = $closingItems->get($employeeKey);

            $anchorItem = $closingItem ?? $openingItem;

            if (! $anchorItem) {
                continue;
            }

            $monthlyBasicSalary = (float) (
                $closingItem?->monthly_rate
                ?: $openingItem?->monthly_rate
                ?: 0
            );

            $calculation = $this->monthlyContributionService->compute(
                (float) ($openingItem?->gross_pay ?? 0),
                (float) ($closingItem?->gross_pay ?? 0),
                $monthlyBasicSalary
            );

            $monthlyKey = $this->monthlyKey(
                $payroll,
                $anchorItem,
                (string) $employeeKey
            );

            $record = $this->resolveCanonicalRecord(
                $payroll,
                $anchorItem,
                $monthlyKey
            );

            $record->fill($this->buildRecord(
                $openingPayroll,
                $payroll,
                $openingItem,
                $closingItem,
                $anchorItem,
                $calculation,
                $monthlyKey,
                $userId,
                $postedAt
            ));
            $record->save();

            $count++;
        }

        return $count;
    }

    private function buildRecord(
        Payroll $openingPayroll,
        Payroll $closingPayroll,
        ?PayrollItem $openingItem,
        ?PayrollItem $closingItem,
        PayrollItem $anchorItem,
        array $calculation,
        string $monthlyKey,
        ?int $userId,
        CarbonInterface $postedAt
    ): array {
        $asset = $anchorItem->employee?->asset
            ?? $anchorItem->employeeBiometric?->activeSalaryProfile?->employee?->asset
            ?? $closingItem?->employee?->asset
            ?? $openingItem?->employee?->asset;

        $sssEmployeeTotal = $this->money($calculation['sss_employee'] ?? 0);
        $sssEmployerTotal = $this->money(
            (float) ($calculation['sss_employer'] ?? 0)
            + (float) ($calculation['sss_ec'] ?? 0)
        );
        $sssTotal = $this->money($sssEmployeeTotal + $sssEmployerTotal);

        $philHealthEmployee = $this->money($calculation['philhealth_employee'] ?? 0);
        $philHealthEmployer = $this->money($calculation['philhealth_employer'] ?? 0);
        $philHealthTotal = $this->money($philHealthEmployee + $philHealthEmployer);

        $pagibigEmployee = $this->money($calculation['pagibig_employee'] ?? 0);
        $pagibigEmployer = $this->money($calculation['pagibig_employer'] ?? 0);
        $pagibigTotal = $this->money($pagibigEmployee + $pagibigEmployer);

        $employeeTotal = $this->money(
            $sssEmployeeTotal
            + $philHealthEmployee
            + $pagibigEmployee
        );

        $employerTotal = $this->money(
            $sssEmployerTotal
            + $philHealthEmployer
            + $pagibigEmployer
        );

        return [
            'monthly_key' => $monthlyKey,
            'payroll_id' => $anchorItem->payroll_id,
            'payroll_item_id' => $anchorItem->id,
            'employee_biometric_id' => $anchorItem->employee_biometric_id,
            'employee_id' => $anchorItem->employee_id,
            'payroll_employee_salary_id' => $anchorItem->payroll_employee_salary_id,
            'posted_by' => $userId,
            'garage_group' => $closingPayroll->garage_group,
            'contribution_month' => (int) $closingPayroll->contribution_month,
            'contribution_year' => (int) $closingPayroll->contribution_year,
            'period_start' => $openingPayroll->period_start,
            'period_end' => $closingPayroll->period_end,
            'payroll_number' => $openingPayroll->payroll_number.' + '.$closingPayroll->payroll_number,
            'employee_no' => $anchorItem->employee_no,
            'employee_name' => $anchorItem->employee_name,
            'company_name' => $anchorItem->company_name_snapshot ?: $anchorItem->employeeBiometric?->company?->name,
            'sss_number' => $asset?->sss_number,
            'philhealth_number' => $asset?->philhealth_number,
            'pagibig_number' => $asset?->pagibig_number,
            'gross_compensation' => $this->money($calculation['monthly_cycle_gross'] ?? 0),
            'business_first_cutoff_gross' => $this->money($calculation['business_first_cutoff_gross'] ?? 0),
            'business_second_cutoff_gross' => $this->money($calculation['business_second_cutoff_gross'] ?? 0),
            'monthly_basic_salary' => $this->money($calculation['fixed_monthly_basic_salary'] ?? 0),

            // Exact SSS Circular No. 2024-006 monthly breakdown.
            'sss_compensation_basis' => $this->money($calculation['sss_basis'] ?? 0),
            'sss_compensation_range_minimum' => $this->money($calculation['sss_compensation_range_minimum'] ?? 0),
            'sss_compensation_range_maximum' => isset($calculation['sss_compensation_range_maximum'])
                ? $this->money($calculation['sss_compensation_range_maximum'])
                : null,
            'sss_msc' => $this->money($calculation['sss_msc'] ?? 0),
            'sss_regular_ss_msc' => $this->money($calculation['sss_regular_ss_msc'] ?? 0),
            'sss_mpf_msc' => $this->money($calculation['sss_mpf_msc'] ?? 0),
            'sss_employee_regular_ss' => $this->money($calculation['sss_employee_regular_ss'] ?? 0),
            'sss_employee_mpf' => $this->money($calculation['sss_employee_mpf'] ?? 0),
            'sss_employee_total' => $sssEmployeeTotal,
            'sss_employer_regular_ss' => $this->money($calculation['sss_employer_regular_ss'] ?? 0),
            'sss_employer_mpf' => $this->money($calculation['sss_employer_mpf'] ?? 0),
            'sss_employer_ec' => $this->money($calculation['sss_ec'] ?? 0),
            'sss_employer_total' => $sssEmployerTotal,
            'sss_total_contribution' => $sssTotal,

            // Exact monthly PhilHealth obligation.
            'philhealth_basis' => $this->money($calculation['philhealth_basis'] ?? 0),
            'philhealth_salary_base' => $this->money($calculation['philhealth_salary_base'] ?? 0),
            'philhealth_premium_rate' => (float) ($calculation['philhealth_premium_rate'] ?? 0.05),
            'philhealth_employee' => $philHealthEmployee,
            'philhealth_employer' => $philHealthEmployer,
            'philhealth_total' => $philHealthTotal,

            // Exact monthly Pag-IBIG / HDMF obligation.
            'pagibig_basis' => $this->money($calculation['pagibig_basis'] ?? 0),
            'pagibig_fund_salary' => $this->money($calculation['pagibig_fund_salary'] ?? 0),
            'pagibig_employee_rate' => (float) ($calculation['pagibig_employee_rate'] ?? 0),
            'pagibig_employer_rate' => (float) ($calculation['pagibig_employer_rate'] ?? 0.02),
            'pagibig_employee' => $pagibigEmployee,
            'pagibig_employer' => $pagibigEmployer,
            'pagibig_total' => $pagibigTotal,

            'employee_total' => $employeeTotal,
            'employer_total' => $employerTotal,
            'grand_total' => $this->money($employeeTotal + $employerTotal),
            'posted_at' => $postedAt,
            'meta' => [
                'source' => 'monthly_payroll_finalize',
                'monthly_record' => true,
                'cycle_complete' => true,
                'cycle_rule' => 'business_1st_26_10_plus_business_2nd_11_25',
                'opening_payroll' => [
                    'id' => $openingPayroll->id,
                    'payroll_number' => $openingPayroll->payroll_number,
                    'payroll_item_id' => $openingItem?->id,
                    'period_start' => $openingPayroll->period_start?->toDateString(),
                    'period_end' => $openingPayroll->period_end?->toDateString(),
                    'gross' => $this->money($openingItem?->gross_pay ?? 0),
                ],
                'closing_payroll' => [
                    'id' => $closingPayroll->id,
                    'payroll_number' => $closingPayroll->payroll_number,
                    'payroll_item_id' => $closingItem?->id,
                    'period_start' => $closingPayroll->period_start?->toDateString(),
                    'period_end' => $closingPayroll->period_end?->toDateString(),
                    'gross' => $this->money($closingItem?->gross_pay ?? 0),
                ],
                'sss_formula' => [
                    'compensation' => $this->money($calculation['sss_basis'] ?? 0),
                    'msc' => $this->money($calculation['sss_msc'] ?? 0),
                    'regular_ss_msc' => $this->money($calculation['sss_regular_ss_msc'] ?? 0),
                    'mpf_msc' => $this->money($calculation['sss_mpf_msc'] ?? 0),
                    'employee_rate' => '5%',
                    'employer_rate' => '10%',
                    'ec' => $this->money($calculation['sss_ec'] ?? 0),
                ],
                'references' => [
                    'sss' => [
                        'circular' => (string) ($calculation['sss_circular_number'] ?? '2024-006'),
                        'effective_from' => (string) ($calculation['sss_effective_from'] ?? '2025-01-01'),
                    ],
                    'philhealth' => [
                        'law' => 'RA 11223',
                        'advisory' => 'PhilHealth Advisory 2025-0002',
                        'rate' => '5%',
                        'income_floor' => 10000,
                        'income_ceiling' => 100000,
                    ],
                    'pagibig' => [
                        'circular' => 'Pag-IBIG Fund Circular No. 460',
                        'maximum_fund_salary' => 10000,
                    ],
                ],
            ],
        ];
    }

    /**
     * Reuse the best legacy row for the employee/month and remove any other
     * old per-cutoff rows before saving the canonical monthly row.
     */
    private function resolveCanonicalRecord(
        Payroll $closingPayroll,
        PayrollItem $anchorItem,
        string $monthlyKey
    ): BenefitContributionRecord {
        $query = BenefitContributionRecord::query()
            ->where('contribution_month', (int) $closingPayroll->contribution_month)
            ->where('contribution_year', (int) $closingPayroll->contribution_year)
            ->where('garage_group', (int) $closingPayroll->garage_group)
            ->where(function ($query) use ($anchorItem): void {
                if ($anchorItem->employee_biometric_id) {
                    $query->where('employee_biometric_id', (int) $anchorItem->employee_biometric_id);

                    return;
                }

                if ($anchorItem->employee_id) {
                    $query->where('employee_id', (int) $anchorItem->employee_id);

                    return;
                }

                $query->where('employee_no', $anchorItem->employee_no);
            })
            ->orderByDesc('id');

        /** @var Collection<int, BenefitContributionRecord> $existing */
        $existing = $query->get();

        $record = $existing->firstWhere('monthly_key', $monthlyKey)
            ?? $existing->firstWhere('payroll_item_id', $anchorItem->id)
            ?? $existing->first()
            ?? new BenefitContributionRecord();

        $existing
            ->reject(fn (BenefitContributionRecord $candidate): bool => $candidate->is($record))
            ->each(fn (BenefitContributionRecord $candidate) => $candidate->delete());

        return $record;
    }

    private function keyItems(Collection $items): Collection
    {
        return $items
            ->mapWithKeys(function (PayrollItem $item): array {
                $key = $this->employeeKey($item);

                return $key !== null ? [$key => $item] : [];
            });
    }

    private function employeeKey(PayrollItem $item): ?string
    {
        if ($item->employee_biometric_id) {
            return 'biometric:'.$item->employee_biometric_id;
        }

        if ($item->employee_id) {
            return 'employee:'.$item->employee_id;
        }

        if (trim((string) $item->employee_no) !== '') {
            return 'employee_no:'.trim((string) $item->employee_no);
        }

        if (trim((string) $item->biometric_employee_id) !== '') {
            return 'bio_source:'.trim((string) $item->biometric_employee_id);
        }

        if (trim((string) $item->employee_name) !== '') {
            return 'name:'.mb_strtolower(trim((string) $item->employee_name));
        }

        return null;
    }

    private function monthlyKey(Payroll $payroll, PayrollItem $item, string $employeeKey): string
    {
        return sprintf(
            'benefit:%d:%04d-%02d:%s',
            (int) $payroll->garage_group,
            (int) $payroll->contribution_year,
            (int) $payroll->contribution_month,
            sha1($employeeKey.'|'.(string) $item->employee_no)
        );
    }

    private function money(mixed $value): float
    {
        return round(max(0, (float) $value), 2);
    }
}
