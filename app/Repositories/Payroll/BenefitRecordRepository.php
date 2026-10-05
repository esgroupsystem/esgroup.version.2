<?php

declare(strict_types=1);

namespace App\Repositories\Payroll;

use App\Models\BenefitContributionRecord;
use App\Models\EmployeeBiometric;
use App\Repositories\Contracts\Payroll\BenefitRecordRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class BenefitRecordRepository implements BenefitRecordRepositoryInterface
{
    private const TOTAL_COLUMNS = [
        'sss_employee' => 'sss_employee_total',
        'sss_employee_collected' => 'sss_employee_collected',
        'sss_employer' => 'sss_employer_total',
        'sss_total' => 'sss_total_contribution',
        'philhealth_employee' => 'philhealth_employee',
        'philhealth_employee_collected' => 'philhealth_employee_collected',
        'philhealth_employer' => 'philhealth_employer',
        'philhealth_total' => 'philhealth_total',
        'pagibig_employee' => 'pagibig_employee',
        'pagibig_employee_collected' => 'pagibig_employee_collected',
        'pagibig_employer' => 'pagibig_employer',
        'pagibig_total' => 'pagibig_total',
        'employee_total' => 'employee_total',
        'employer_total' => 'employer_total',
        'grand_total' => 'grand_total',
        'employee_share_unrecovered' => 'employee_share_unrecovered',
    ];

    public function paginatePeople(int $month, int $year, string $search, ?int $group, string|array|null $allowedGroups, int $perPage = 25): LengthAwarePaginator
    {
        return $this->ledgerPeople($month, $year, $search, $group, $allowedGroups)
            ->payrollDirectoryOrder()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function people(int $month, int $year, string $search, ?int $group, string|array|null $allowedGroups): Collection
    {
        return $this->ledgerPeople($month, $year, $search, $group, $allowedGroups)->payrollDirectoryOrder()->get();
    }

    public function countActivePeople(string $search, ?int $group, string|array|null $allowedGroups): int
    {
        return $this->scopedPeople($search, $group, $allowedGroups)->payrollActive()->count();
    }

    public function recordsFor(int $month, int $year, Collection $employeeBiometricIds, string $order): Collection
    {
        if ($employeeBiometricIds->isEmpty()) {
            return new \Illuminate\Database\Eloquent\Collection;
        }

        return $this->month($month, $year)
            ->with('payroll:id,payroll_number,status,finalized_at')
            ->whereIn('employee_biometric_id', $employeeBiometricIds)
            ->when(
                $order === 'posted',
                fn (Builder $query) => $query->orderBy('posted_at'),
                fn (Builder $query) => $query->orderBy('company_name')->orderBy('employee_name')->orderBy('period_end'),
            )
            ->get();
    }

    public function totals(int $month, int $year, string $search, ?int $group, string|array|null $allowedGroups): object
    {
        $query = $this->month($month, $year)
            ->whereIn('employee_biometric_id', $this->ledgerPeople($month, $year, $search, $group, $allowedGroups)->select('employee_biometrics.id'));
        foreach (self::TOTAL_COLUMNS as $alias => $column) {
            $query->selectRaw("COALESCE(SUM({$column}), 0) as {$alias}");
        }

        return $query->toBase()->first() ?? (object) [];
    }

    public function countPostedPeople(int $month, int $year, string $search, ?int $group, string|array|null $allowedGroups, bool $activeOnly): int
    {
        $people = $this->ledgerPeople($month, $year, $search, $group, $allowedGroups)
            ->when($activeOnly, fn (Builder $query) => $query->payrollActive())
            ->select('employee_biometrics.id');

        return $this->month($month, $year)
            ->whereIn('employee_biometric_id', $people)
            ->distinct('employee_biometric_id')
            ->count('employee_biometric_id');
    }

    /** @return Builder<BenefitContributionRecord> */
    private function month(int $month, int $year): Builder
    {
        return BenefitContributionRecord::query()->where('contribution_month', $month)->where('contribution_year', $year);
    }

    /**
     * @param  string|list<int|string>|null  $allowedGroups
     * @return Builder<EmployeeBiometric>
     */
    private function ledgerPeople(int $month, int $year, string $search, ?int $group, string|array|null $allowedGroups): Builder
    {
        // Separated people stay visible for any month with a posted record.
        return $this->scopedPeople($search, $group, $allowedGroups)
            ->where(fn (Builder $query) => $query
                ->where(fn (Builder $active) => $active->payrollActive())
                ->orWhereHas('benefitContributionRecords', fn (Builder $records) => $records
                    ->where('contribution_month', $month)
                    ->where('contribution_year', $year)));
    }

    /**
     * @param  string|list<int|string>|null  $allowedGroups
     * @return Builder<EmployeeBiometric>
     */
    private function scopedPeople(string $search, ?int $group, string|array|null $allowedGroups): Builder
    {
        $query = EmployeeBiometric::query()->with(['company', 'activeSalaryProfile.employee.asset']);

        if ($allowedGroups !== 'all') {
            $allowed = collect($allowedGroups ?? [])
                ->map(fn ($value): int => (int) $value)
                ->filter(fn (int $value): bool => in_array($value, array_keys(EmployeeBiometric::GROUP_LABELS), true))
                ->unique()
                ->values()
                ->all();
            if ($allowed === [] || ($group !== null && ! in_array($group, $allowed, true))) {
                return $query->whereRaw('1 = 0');
            }
            $query->whereIn('group_name', $allowed);
        }

        return $query
            ->when($group !== null, fn (Builder $query) => $query->where('group_name', $group))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('display_name', 'like', "%{$search}%")
                ->orWhere('source_employee_name', 'like', "%{$search}%")
                ->orWhere('source_crosschex_account_name', 'like', "%{$search}%")
                ->orWhere('display_employee_no', 'like', "%{$search}%")
                ->orWhere('source_employee_no', 'like', "%{$search}%")
                ->orWhere('source_employee_id', 'like', "%{$search}%")
                ->orWhereHas('company', fn (Builder $company) => $company->where('name', 'like', "%{$search}%"))));
    }
}
