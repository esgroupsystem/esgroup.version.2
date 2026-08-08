<?php

namespace App\Services\Payroll;

use App\Models\BenefitContributionRecord;
use App\Models\EmployeeBiometric;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BenefitRecordsService
{
    public function buildIndex(array $filters, string|array|null $allowedGroups): array
    {
        $month = (int) $filters['month'];
        $year = (int) $filters['year'];
        $search = trim((string) ($filters['search'] ?? ''));
        $garageGroup = isset($filters['garage_group']) ? (int) $filters['garage_group'] : null;

        $employeeQuery = EmployeeBiometric::query()
            ->payrollActive()
            ->with([
                'company',
                'activeSalaryProfile.employee.asset',
            ]);

        $this->applyGroupAccess($employeeQuery, $allowedGroups, $garageGroup);
        $this->applySearch($employeeQuery, $search);

        $employeeQuery
            ->orderByRaw("COALESCE(NULLIF(display_name, ''), NULLIF(source_employee_name, ''), source_crosschex_account_name, source_crosschex_account, source_key)")
            ->orderBy('id');

        $activeEmployeeCount = (clone $employeeQuery)->count();

        /** @var LengthAwarePaginator $employees */
        $employees = (clone $employeeQuery)
            ->paginate(25)
            ->withQueryString();

        $pageEmployeeIds = $employees->getCollection()
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values();

        $recordsByEmployee = BenefitContributionRecord::query()
            ->with('payroll:id,payroll_number,status,finalized_at')
            ->where('contribution_month', $month)
            ->where('contribution_year', $year)
            ->whereIn('employee_biometric_id', $pageEmployeeIds)
            ->orderBy('posted_at')
            ->get()
            ->groupBy('employee_biometric_id');

        $employees->setCollection(
            $employees->getCollection()->map(function (EmployeeBiometric $employee) use ($recordsByEmployee): array {
                /** @var Collection<int, BenefitContributionRecord> $records */
                $records = $recordsByEmployee->get($employee->id, collect());
                $asset = $employee->activeSalaryProfile?->employee?->asset;

                return [
                    'employee' => $employee,
                    'records' => $records,
                    'summary' => $this->summarize($records),
                    'identifiers' => [
                        'sss' => $records->pluck('sss_number')->filter()->last() ?: $asset?->sss_number,
                        'philhealth' => $records->pluck('philhealth_number')->filter()->last() ?: $asset?->philhealth_number,
                        'pagibig' => $records->pluck('pagibig_number')->filter()->last() ?: $asset?->pagibig_number,
                    ],
                ];
            })
        );

        $filteredEmployeeIds = (clone $employeeQuery)
            ->reorder()
            ->select('employee_biometrics.id');

        $recordTotalsQuery = BenefitContributionRecord::query()
            ->where('contribution_month', $month)
            ->where('contribution_year', $year)
            ->whereIn('employee_biometric_id', $filteredEmployeeIds);

        $totals = (clone $recordTotalsQuery)
            ->selectRaw('COALESCE(SUM(sss_employee_total), 0) as sss_employee')
            ->selectRaw('COALESCE(SUM(sss_employer_total), 0) as sss_employer')
            ->selectRaw('COALESCE(SUM(sss_total_contribution), 0) as sss_total')
            ->selectRaw('COALESCE(SUM(philhealth_employee), 0) as philhealth_employee')
            ->selectRaw('COALESCE(SUM(philhealth_employer), 0) as philhealth_employer')
            ->selectRaw('COALESCE(SUM(philhealth_total), 0) as philhealth_total')
            ->selectRaw('COALESCE(SUM(pagibig_employee), 0) as pagibig_employee')
            ->selectRaw('COALESCE(SUM(pagibig_employer), 0) as pagibig_employer')
            ->selectRaw('COALESCE(SUM(pagibig_total), 0) as pagibig_total')
            ->selectRaw('COALESCE(SUM(employee_total), 0) as employee_total')
            ->selectRaw('COALESCE(SUM(employer_total), 0) as employer_total')
            ->selectRaw('COALESCE(SUM(grand_total), 0) as grand_total')
            ->first();

        $postedEmployeeCount = (clone $recordTotalsQuery)
            ->distinct('employee_biometric_id')
            ->count('employee_biometric_id');

        return [
            'employees' => $employees,
            'totals' => $totals,
            'activeEmployeeCount' => $activeEmployeeCount,
            'postedEmployeeCount' => $postedEmployeeCount,
            'notPostedEmployeeCount' => max(0, $activeEmployeeCount - $postedEmployeeCount),
            'groupOptions' => $this->groupOptions($allowedGroups),
        ];
    }

    public function buildOverall(array $filters, string|array|null $allowedGroups): array
    {
        $month = (int) $filters['month'];
        $year = (int) $filters['year'];
        $search = trim((string) ($filters['search'] ?? ''));
        $garageGroup = isset($filters['garage_group']) ? (int) $filters['garage_group'] : null;

        $employeeQuery = EmployeeBiometric::query()
            ->payrollActive()
            ->with([
                'company',
                'activeSalaryProfile.employee.asset',
            ]);

        $this->applyGroupAccess($employeeQuery, $allowedGroups, $garageGroup);
        $this->applySearch($employeeQuery, $search);

        $employeeQuery
            ->orderByRaw("COALESCE(NULLIF(display_name, ''), NULLIF(source_employee_name, ''), source_crosschex_account_name, source_crosschex_account, source_key)")
            ->orderBy('id');

        $employees = (clone $employeeQuery)->get();
        $activeEmployeeCount = $employees->count();
        $employeeIds = $employees->pluck('id')->map(fn ($id): int => (int) $id)->values();

        $records = BenefitContributionRecord::query()
            ->with('payroll:id,payroll_number,status,finalized_at')
            ->where('contribution_month', $month)
            ->where('contribution_year', $year)
            ->when(
                $employeeIds->isNotEmpty(),
                fn (Builder $query) => $query->whereIn('employee_biometric_id', $employeeIds),
                fn (Builder $query) => $query->whereRaw('1 = 0')
            )
            ->orderBy('company_name')
            ->orderBy('employee_name')
            ->orderBy('period_end')
            ->get();

        $recordsByEmployee = $records->groupBy('employee_biometric_id');

        $rows = $employees->map(function (EmployeeBiometric $employee) use ($recordsByEmployee): array {
            /** @var Collection<int, BenefitContributionRecord> $employeeRecords */
            $employeeRecords = $recordsByEmployee->get($employee->id, collect());
            $asset = $employee->activeSalaryProfile?->employee?->asset;

            return [
                'employee' => $employee,
                'records' => $employeeRecords,
                'summary' => $this->summarize($employeeRecords),
                'identifiers' => [
                    'sss' => $employeeRecords->pluck('sss_number')->filter()->last() ?: $asset?->sss_number,
                    'philhealth' => $employeeRecords->pluck('philhealth_number')->filter()->last() ?: $asset?->philhealth_number,
                    'pagibig' => $employeeRecords->pluck('pagibig_number')->filter()->last() ?: $asset?->pagibig_number,
                ],
                'company_name' => $employeeRecords->pluck('company_name')->filter()->last()
                    ?: $employee->company?->name
                    ?: 'No company',
            ];
        });

        $postedEmployeeCount = $records
            ->pluck('employee_biometric_id')
            ->filter()
            ->unique()
            ->count();

        $companyTotals = $records
            ->groupBy(fn (BenefitContributionRecord $record): string => trim((string) $record->company_name) !== ''
                ? (string) $record->company_name
                : 'No company')
            ->map(function (Collection $companyRecords, string $companyName): array {
                return [
                    'company_name' => $companyName,
                    'employee_count' => $companyRecords
                        ->pluck('employee_biometric_id')
                        ->filter()
                        ->unique()
                        ->count(),
                    'totals' => $this->aggregateTotals($companyRecords),
                ];
            })
            ->sortBy('company_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return [
            'rows' => $rows,
            'records' => $records,
            'totals' => $this->aggregateTotals($records),
            'companyTotals' => $companyTotals,
            'activeEmployeeCount' => $activeEmployeeCount,
            'postedEmployeeCount' => $postedEmployeeCount,
            'notPostedEmployeeCount' => max(0, $activeEmployeeCount - $postedEmployeeCount),
            'groupOptions' => $this->groupOptions($allowedGroups),
            'payrollNumbers' => $records->pluck('payroll_number')->filter()->unique()->sort()->values(),
        ];
    }

    private function aggregateTotals(Collection $records): array
    {
        $sum = static fn (string $field): float => round((float) $records->sum($field), 2);

        return [
            'sss_employee' => $sum('sss_employee_total'),
            'sss_employer' => $sum('sss_employer_total'),
            'sss_total' => $sum('sss_total_contribution'),
            'philhealth_employee' => $sum('philhealth_employee'),
            'philhealth_employer' => $sum('philhealth_employer'),
            'philhealth_total' => $sum('philhealth_total'),
            'pagibig_employee' => $sum('pagibig_employee'),
            'pagibig_employer' => $sum('pagibig_employer'),
            'pagibig_total' => $sum('pagibig_total'),
            'employee_total' => $sum('employee_total'),
            'employer_total' => $sum('employer_total'),
            'grand_total' => $sum('grand_total'),
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
            'sss_employer_regular_ss' => $sum('sss_employer_regular_ss'),
            'sss_employer_mpf' => $sum('sss_employer_mpf'),
            'sss_employer_ec' => $sum('sss_employer_ec'),
            'sss_employer_total' => $sum('sss_employer_total'),
            'sss_total_contribution' => $sum('sss_total_contribution'),
            'philhealth_basis' => $max('philhealth_basis'),
            'philhealth_salary_base' => $max('philhealth_salary_base'),
            'philhealth_employee' => $sum('philhealth_employee'),
            'philhealth_employer' => $sum('philhealth_employer'),
            'philhealth_total' => $sum('philhealth_total'),
            'pagibig_basis' => $max('pagibig_basis'),
            'pagibig_fund_salary' => $max('pagibig_fund_salary'),
            'pagibig_employee_rate' => (float) ($records->max('pagibig_employee_rate') ?? 0),
            'pagibig_employer_rate' => (float) ($records->max('pagibig_employer_rate') ?? 0),
            'pagibig_employee' => $sum('pagibig_employee'),
            'pagibig_employer' => $sum('pagibig_employer'),
            'pagibig_total' => $sum('pagibig_total'),
            'employee_total' => $sum('employee_total'),
            'employer_total' => $sum('employer_total'),
            'grand_total' => $grandTotal,
        ];
    }

    private function applyGroupAccess(
        Builder $query,
        string|array|null $allowedGroups,
        ?int $requestedGroup
    ): void {
        if ($allowedGroups !== 'all') {
            $allowedGroups = collect($allowedGroups ?? [])
                ->map(fn ($group): int => (int) $group)
                ->filter(fn (int $group): bool => in_array($group, [1, 2], true))
                ->unique()
                ->values()
                ->all();

            if ($allowedGroups === []) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->whereIn('group_name', $allowedGroups);
        }

        if ($requestedGroup !== null) {
            if ($allowedGroups !== 'all' && ! in_array($requestedGroup, $allowedGroups, true)) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->where('group_name', $requestedGroup);
        }
    }

    private function applySearch(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('display_name', 'like', "%{$search}%")
                ->orWhere('source_employee_name', 'like', "%{$search}%")
                ->orWhere('source_crosschex_account_name', 'like', "%{$search}%")
                ->orWhere('display_employee_no', 'like', "%{$search}%")
                ->orWhere('source_employee_no', 'like', "%{$search}%")
                ->orWhere('source_employee_id', 'like', "%{$search}%")
                ->orWhereHas('company', fn (Builder $company) => $company->where('name', 'like', "%{$search}%"));
        });
    }

    private function groupOptions(string|array|null $allowedGroups): array
    {
        $all = [
            1 => 'Mirasol / Balintawak Payroll',
            2 => 'Gonzales Payroll',
        ];

        if ($allowedGroups === 'all') {
            return $all;
        }

        return collect($all)
            ->only($allowedGroups ?? [])
            ->all();
    }
}
