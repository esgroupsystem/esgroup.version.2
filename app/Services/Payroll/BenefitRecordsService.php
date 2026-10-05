<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Enums\BenefitProgram;
use App\Models\BenefitContributionRecord;
use App\Models\EmployeeBiometric;
use App\Repositories\Contracts\Payroll\BenefitRecordRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Payroll → Benefits Records / Benefits Overall: each person's posted monthly government
 * contributions (SSS, PhilHealth, Pag-IBIG), totals per company, and the KPI counts.
 */
class BenefitRecordsService
{
    public function __construct(
        private readonly BenefitRecordRepositoryInterface $records,
    ) {}

    /**
     * Benefits Records page: a page of people with their month's records and summary.
     *
     * @param  array<string, mixed>  $filters  month, year, search, garage_group
     * @param  string|list<int|string>|null  $allowedGroups
     * @return array<string, mixed>
     */
    public function buildIndex(array $filters, string|array|null $allowedGroups): array
    {
        [$month, $year, $search, $group] = $this->scope($filters);

        $people = $this->records->paginatePeople($month, $year, $search, $group, $allowedGroups);
        $ids = $people->getCollection()->pluck('id')->map(fn ($id): int => (int) $id)->values();
        $byPerson = $this->records->recordsFor($month, $year, $ids, 'posted')->groupBy('employee_biometric_id');

        $people->setCollection($people->getCollection()->map(function (EmployeeBiometric $person) use ($byPerson): array {
            /** @var Collection<int, BenefitContributionRecord> $records */
            $records = $byPerson->get($person->id, collect());

            return [
                'employee' => $person,
                'records' => $records,
                'summary' => $this->summarize($records),
                'identifiers' => $this->identifiers($person, $records, [
                    BenefitProgram::Sss->value => 'sss',
                    BenefitProgram::PhilHealth->value => 'philhealth',
                    BenefitProgram::PagIbig->value => 'pagibig',
                ]),
            ];
        }));

        $active = $this->records->countActivePeople($search, $group, $allowedGroups);
        $postedActive = $this->records->countPostedPeople($month, $year, $search, $group, $allowedGroups, true);

        return [
            'employees' => $people,
            'totals' => $this->records->totals($month, $year, $search, $group, $allowedGroups),
            'activeEmployeeCount' => $active,
            'postedEmployeeCount' => $this->records->countPostedPeople($month, $year, $search, $group, $allowedGroups, false),
            'notPostedEmployeeCount' => max(0, $active - $postedActive),
            'groupOptions' => $this->groupOptions($allowedGroups),
        ];
    }

    /**
     * Benefits Overall page / print: every person, company totals and grand totals.
     *
     * @param  array<string, mixed>  $filters  month, year, search, garage_group
     * @param  string|list<int|string>|null  $allowedGroups
     * @return array<string, mixed>
     */
    public function buildOverall(array $filters, string|array|null $allowedGroups): array
    {
        [$month, $year, $search, $group] = $this->scope($filters);

        $people = $this->records->people($month, $year, $search, $group, $allowedGroups);
        $records = $this->records->recordsFor($month, $year, $people->pluck('id')->map(fn ($id): int => (int) $id)->values(), 'company');
        $byPerson = $records->groupBy('employee_biometric_id');

        $rows = $people->map(function (EmployeeBiometric $person) use ($byPerson): array {
            /** @var Collection<int, BenefitContributionRecord> $personRecords */
            $personRecords = $byPerson->get($person->id, collect());

            return [
                'employee' => $person,
                'records' => $personRecords,
                'summary' => $this->summarize($personRecords),
                'identifiers' => $this->identifiers($person, $personRecords, ['sss' => 'sss', 'philhealth' => 'philhealth', 'pagibig' => 'pagibig']),
                'company_name' => $personRecords->pluck('company_name')->filter()->last() ?: $person->company?->name ?: 'No company',
            ];
        });

        $activeIds = $people
            ->filter(fn (EmployeeBiometric $person): bool => ($person->is_payroll_active === null || (bool) $person->is_payroll_active)
                && ($person->employment_status === null || $person->employment_status === EmployeeBiometric::STATUS_ACTIVE))
            ->pluck('id')
            ->map(fn ($id): int => (int) $id);
        $postedIds = fn (Collection $set): int => $set->pluck('employee_biometric_id')->filter()->unique()->count();
        $active = $this->records->countActivePeople($search, $group, $allowedGroups);

        $companyTotals = $records
            ->groupBy(fn (BenefitContributionRecord $record): string => trim((string) $record->company_name) !== '' ? (string) $record->company_name : 'No company')
            ->map(fn (Collection $companyRecords, string $company): array => [
                'company_name' => $company,
                'employee_count' => $postedIds($companyRecords),
                'totals' => $this->aggregateTotals($companyRecords),
            ])
            ->sortBy('company_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return [
            'rows' => $rows,
            'records' => $records,
            'totals' => $this->aggregateTotals($records),
            'companyTotals' => $companyTotals,
            'activeEmployeeCount' => $active,
            'postedEmployeeCount' => $postedIds($records),
            'notPostedEmployeeCount' => max(0, $active - $postedIds($records->whereIn('employee_biometric_id', $activeIds))),
            'groupOptions' => $this->groupOptions($allowedGroups),
            'payrollNumbers' => $records->pluck('payroll_number')->filter()->unique()->sort()->values(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{0: int, 1: int, 2: string, 3: int|null}
     */
    private function scope(array $filters): array
    {
        return [
            (int) $filters['month'],
            (int) $filters['year'],
            trim((string) ($filters['search'] ?? '')),
            isset($filters['garage_group']) ? (int) $filters['garage_group'] : null,
        ];
    }

    /**
     * Government ID numbers from the latest record, else the 201 file.
     *
     * @param  Collection<int, BenefitContributionRecord>  $records
     * @param  array<string, string>  $keys  output key => program prefix
     * @return array<string, mixed>
     */
    private function identifiers(EmployeeBiometric $person, Collection $records, array $keys): array
    {
        $asset = $person->activeSalaryProfile?->employee?->asset;

        return collect($keys)
            ->map(fn (string $program): mixed => $records->pluck("{$program}_number")->filter()->last() ?: $asset?->{"{$program}_number"})
            ->all();
    }

    private function aggregateTotals(Collection $records): array
    {
        $sum = static fn (string $field): float => round((float) $records->sum($field), 2);

        return [
            'sss_employee_regular_ss' => $sum('sss_employee_regular_ss'),
            'sss_employer_regular_ss' => $sum('sss_employer_regular_ss'),
            'sss_employer_ec' => $sum('sss_employer_ec'),
            'sss_regular_total' => round(
                $sum('sss_employee_regular_ss')
                + $sum('sss_employer_regular_ss')
                + $sum('sss_employer_ec'),
                2
            ),
            'sss_employee_mpf' => $sum('sss_employee_mpf'),
            'sss_employer_mpf' => $sum('sss_employer_mpf'),
            'sss_mpf_total' => round(
                $sum('sss_employee_mpf') + $sum('sss_employer_mpf'),
                2
            ),
            'sss_employee' => $sum('sss_employee_total'),
            'sss_employee_collected' => $sum('sss_employee_collected'),
            'sss_employer' => $sum('sss_employer_total'),
            'sss_total' => $sum('sss_total_contribution'),
            'philhealth_employee' => $sum('philhealth_employee'),
            'philhealth_employee_collected' => $sum('philhealth_employee_collected'),
            'philhealth_employer' => $sum('philhealth_employer'),
            'philhealth_total' => $sum('philhealth_total'),
            'pagibig_employee' => $sum('pagibig_employee'),
            'pagibig_employee_collected' => $sum('pagibig_employee_collected'),
            'pagibig_employer' => $sum('pagibig_employer'),
            'pagibig_total' => $sum('pagibig_total'),
            'employee_total' => $sum('employee_total'),
            'employee_collected_total' => round(
                $sum('sss_employee_collected') + $sum('philhealth_employee_collected') + $sum('pagibig_employee_collected'),
                2
            ),
            'employer_total' => $sum('employer_total'),
            'grand_total' => $sum('grand_total'),
            'employee_share_unrecovered' => $sum('employee_share_unrecovered'),
        ];
    }

    private function summarize(Collection $records): array
    {
        $sum = static fn (string $field): float => round((float) $records->sum($field), 2);
        $max = static fn (string $field): float => round((float) ($records->max($field) ?? 0), 2);

        $grandTotal = $sum('grand_total');

        return [
            'posted' => $records->isNotEmpty(),
            'has_contribution' => $grandTotal > 0,
            'payroll_numbers' => $records->pluck('payroll_number')->filter()->unique()->values()->all(),
            'posted_at' => $records->max('posted_at'),
            'monthly_basic_salary' => $max('monthly_basic_salary'),
            'gross_compensation' => $max('gross_compensation'),
            'business_first_cutoff_gross' => $max('business_first_cutoff_gross'),
            'business_second_cutoff_gross' => $max('business_second_cutoff_gross'),
            'sss_compensation_basis' => $max('sss_compensation_basis'),
            'sss_compensation_range_minimum' => $max('sss_compensation_range_minimum'),
            'sss_compensation_range_maximum' => $records->pluck('sss_compensation_range_maximum')->filter(fn ($value) => $value !== null)->max(),
            'sss_msc' => $max('sss_msc'),
            'sss_regular_ss_msc' => $max('sss_regular_ss_msc'),
            'sss_mpf_msc' => $max('sss_mpf_msc'),
            'sss_employee_regular_ss' => $sum('sss_employee_regular_ss'),
            'sss_employee_mpf' => $sum('sss_employee_mpf'),
            'sss_employee_total' => $sum('sss_employee_total'),
            'sss_employee_collected' => $sum('sss_employee_collected'),
            'sss_employer_regular_ss' => $sum('sss_employer_regular_ss'),
            'sss_employer_mpf' => $sum('sss_employer_mpf'),
            'sss_employer_ec' => $sum('sss_employer_ec'),
            'sss_employer_total' => $sum('sss_employer_total'),
            'sss_total_contribution' => $sum('sss_total_contribution'),
            'philhealth_basis' => $max('philhealth_basis'),
            'philhealth_salary_base' => $max('philhealth_salary_base'),
            'philhealth_employee' => $sum('philhealth_employee'),
            'philhealth_employee_collected' => $sum('philhealth_employee_collected'),
            'philhealth_employer' => $sum('philhealth_employer'),
            'philhealth_total' => $sum('philhealth_total'),
            'pagibig_basis' => $max('pagibig_basis'),
            'pagibig_fund_salary' => $max('pagibig_fund_salary'),
            'pagibig_employee_rate' => (float) ($records->max('pagibig_employee_rate') ?? 0),
            'pagibig_employer_rate' => (float) ($records->max('pagibig_employer_rate') ?? 0),
            'pagibig_employee' => $sum('pagibig_employee'),
            'pagibig_employee_collected' => $sum('pagibig_employee_collected'),
            'pagibig_employer' => $sum('pagibig_employer'),
            'pagibig_total' => $sum('pagibig_total'),
            'employee_total' => $sum('employee_total'),
            'employee_collected_total' => round(
                $sum('sss_employee_collected') + $sum('philhealth_employee_collected') + $sum('pagibig_employee_collected'),
                2
            ),
            'employer_total' => $sum('employer_total'),
            'grand_total' => $grandTotal,
            'employee_share_unrecovered' => $sum('employee_share_unrecovered'),
            'settlement_status' => (string) ($records->pluck('settlement_status')->filter()->last() ?? 'not_posted'),
            'settlement_meta' => (array) ($records->pluck('settlement_meta')->filter()->last() ?? []),
        ];
    }

    /**
     * @param  string|list<int|string>|null  $allowedGroups
     * @return array<int, string> group => label, only the allowed groups
     */
    private function groupOptions(string|array|null $allowedGroups): array
    {
        return $allowedGroups === 'all'
            ? EmployeeBiometric::GROUP_LABELS
            : collect(EmployeeBiometric::GROUP_LABELS)->only($allowedGroups ?? [])->all();
    }
}
