<?php

namespace App\Services\Biometrics;

use App\Models\EmployeeBiometric;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EmployeeBiometricService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return EmployeeBiometric::query()
            ->with('company')
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('display_name', 'like', "%{$search}%")
                        ->orWhere('display_employee_no', 'like', "%{$search}%")
                        ->orWhere('source_employee_name', 'like', "%{$search}%")
                        ->orWhere('source_employee_no', 'like', "%{$search}%")
                        ->orWhere('source_crosschex_id', 'like', "%{$search}%");
                });
            })
            ->when($filters['employment_status'] ?? null, function (Builder $query, string $status): void {
                $query->where('employment_status', $status);
            })
            ->when($filters['biometric_company_id'] ?? null, function (Builder $query, string $companyId): void {
                $query->where('biometric_company_id', $companyId);
            })
            ->latest('last_check_time')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * @return array<string, int>
     */
    public function counts(): array
    {
        return [
            'total' => EmployeeBiometric::query()->count(),
            'active' => EmployeeBiometric::query()->active()->count(),
            'inactive' => EmployeeBiometric::query()->inactive()->count(),
            'without_company' => EmployeeBiometric::query()
                ->whereNull('biometric_company_id')
                ->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateManualFields(EmployeeBiometric $employeeBiometric, array $data): EmployeeBiometric
    {
        $employeeBiometric->update([
            'biometric_company_id' => $data['biometric_company_id'] ?? null,
            'display_employee_no' => $data['display_employee_no'] ?? null,
            'display_name' => $data['display_name'],
            'employment_status' => $data['employment_status'],
            'remarks' => $data['remarks'] ?? null,
        ]);

        return $employeeBiometric->refresh();
    }
}
