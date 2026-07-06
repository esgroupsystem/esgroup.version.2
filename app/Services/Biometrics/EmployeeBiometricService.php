<?php

namespace App\Services\Biometrics;

use App\Models\EmployeeBiometric;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EmployeeBiometricService
{
    public function __construct(
        private readonly EmployeeBiometricIdentityService $identityService
    ) {}

    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = trim((string) ($filters['employment_status'] ?? ''));
        $companyId = trim((string) ($filters['biometric_company_id'] ?? ''));
        $groupName = trim((string) ($filters['group_name'] ?? ''));
        $payrollActive = trim((string) ($filters['payroll_active'] ?? ''));

        return EmployeeBiometric::query()
            ->with('company')
            ->withCount([
                'attendanceSummaries',
                'attendanceAdjustments',
                'payrollItems',
                'salaryProfiles',
                'plottingSchedules',
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('display_employee_no', 'like', "%{$search}%")
                        ->orWhere('display_name', 'like', "%{$search}%")
                        ->orWhere('source_employee_no', 'like', "%{$search}%")
                        ->orWhere('source_employee_id', 'like', "%{$search}%")
                        ->orWhere('source_employee_name', 'like', "%{$search}%")
                        ->orWhere('source_crosschex_id', 'like', "%{$search}%")
                        ->orWhere('source_crosschex_account_name', 'like', "%{$search}%")
                        ->orWhere('source_crosschex_account', 'like', "%{$search}%")
                        ->orWhere('group_name', 'like', "%{$search}%")
                        ->orWhere('device_name', 'like', "%{$search}%")
                        ->orWhere('device_sn', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn ($query) => $query->where('employment_status', $status))
            ->when($companyId !== '', fn ($query) => $query->where('biometric_company_id', (int) $companyId))
            ->when($groupName !== '', fn ($query) => $query->where('group_name', $groupName))
            ->when($payrollActive !== '', fn ($query) => $query->where('is_payroll_active', (bool) (int) $payrollActive))
            ->orderByRaw("CASE WHEN employment_status = 'active' AND is_payroll_active = 1 THEN 0 ELSE 1 END")
            ->orderBy('group_name')
            ->orderByRaw("COALESCE(NULLIF(display_name, ''), NULLIF(source_employee_name, ''), NULLIF(source_crosschex_account_name, '')) ASC")
            ->paginate($perPage)
            ->withQueryString();
    }

    public function counts(): array
    {
        return [
            'total' => EmployeeBiometric::query()->count(),

            'payroll_active' => EmployeeBiometric::query()
                ->payrollActive()
                ->count(),

            'active' => EmployeeBiometric::query()
                ->where('employment_status', EmployeeBiometric::STATUS_ACTIVE)
                ->count(),

            'inactive' => EmployeeBiometric::query()
                ->inactive()
                ->count(),

            'without_company' => EmployeeBiometric::query()
                ->whereNull('biometric_company_id')
                ->count(),

            'with_salary' => EmployeeBiometric::query()
                ->has('salaryProfiles')
                ->count(),

            'with_schedule' => EmployeeBiometric::query()
                ->has('plottingSchedules')
                ->count(),

            'groups' => EmployeeBiometric::query()
                ->select('group_name', DB::raw('COUNT(*) AS total'))
                ->whereNotNull('group_name')
                ->where('group_name', '!=', '')
                ->groupBy('group_name')
                ->orderBy('group_name')
                ->pluck('total', 'group_name')
                ->toArray(),
        ];
    }

    public function groups(): array
    {
        return EmployeeBiometric::query()
            ->whereNotNull('group_name')
            ->where('group_name', '!=', '')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name')
            ->toArray();
    }

    public function updateManualFields(EmployeeBiometric $employeeBiometric, array $data): EmployeeBiometric
    {
        $status = $this->identityService->clean(
            $data['employment_status'] ?? $employeeBiometric->employment_status
        ) ?: EmployeeBiometric::STATUS_ACTIVE;

        $isPayrollActive = (bool) ($data['is_payroll_active'] ?? false);

        if ($status !== EmployeeBiometric::STATUS_ACTIVE) {
            $isPayrollActive = false;
        }

        $payload = [
            'biometric_company_id' => $data['biometric_company_id'] ?? $employeeBiometric->biometric_company_id,
            'display_employee_no' => $this->identityService->clean($data['display_employee_no'] ?? null),
            'display_name' => $this->identityService->clean($data['display_name'] ?? null),
            'employment_status' => $status,
            'group_name' => $this->identityService->clean($data['group_name'] ?? null),
            'is_payroll_active' => $isPayrollActive,
            'inactive_at' => $isPayrollActive ? null : ($employeeBiometric->inactive_at ?? now('Asia/Manila')),
            'remarks' => $this->identityService->clean($data['remarks'] ?? null),
        ];

        $payload['employee_identity_hash'] = $this->identityService->identityHash(array_merge(
            $employeeBiometric->toArray(),
            $payload
        ));

        $employeeBiometric->update($payload);

        return $employeeBiometric->refresh();
    }
}
