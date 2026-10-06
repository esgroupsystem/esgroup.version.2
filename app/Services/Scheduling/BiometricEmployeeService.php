<?php

declare(strict_types=1);

namespace App\Services\Scheduling;

use App\Models\BiometricCompany;
use App\Models\EmployeeBiometric;
use App\Repositories\Contracts\Biometrics\BiometricCompanyRepositoryInterface;
use App\Repositories\Contracts\Biometrics\EmployeeBiometricRepositoryInterface;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use App\Services\Biometrics\EmployeeBiometricSyncService;
use App\Services\HR\BiometricLinkService;
use App\Services\Payroll\PayrollAuditService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Scheduling & Rates → Employees: the biometric (CrossChex) people that payroll runs on —
 * list, counts, the CrossChex sync and the manual fields (company, group, status, HR link).
 */
final class BiometricEmployeeService
{
    public const FILTERS = ['search', 'employment_status', 'biometric_company_id', 'group_name', 'payroll_active'];

    public function __construct(
        private readonly EmployeeBiometricRepositoryInterface $biometrics,
        private readonly BiometricCompanyRepositoryInterface $companies,
        private readonly EmployeeBiometricIdentityService $identity,
        private readonly EmployeeBiometricSyncService $sync,
        private readonly BiometricLinkService $links,
        private readonly PayrollAuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array{search: string, employment_status: string, biometric_company_id: string, group_name: string, payroll_active: string}
     */
    public function filters(array $input): array
    {
        $filters = [];
        foreach (self::FILTERS as $key) {
            $filters[$key] = trim((string) ($input[$key] ?? ''));
        }

        /** @var array{search: string, employment_status: string, biometric_company_id: string, group_name: string, payroll_active: string} $filters */
        return $filters;
    }

    /**
     * @param  array{search: string, employment_status: string, biometric_company_id: string, group_name: string, payroll_active: string}  $filters
     * @return LengthAwarePaginator<int, EmployeeBiometric>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->biometrics->paginateDirectory($filters);
    }

    /** @return array{total: int, active: int, payroll_active: int, inactive: int, without_company: int} */
    public function counts(): array
    {
        return [
            'total' => $this->biometrics->count(),
            'active' => $this->biometrics->countWithStatus(EmployeeBiometric::STATUS_ACTIVE),
            'payroll_active' => $this->biometrics->countPayrollActive(),
            'inactive' => $this->biometrics->countInactive(),
            'without_company' => $this->biometrics->countWithoutCompany(),
        ];
    }

    /** @return Collection<int, string> */
    public function groups(): Collection
    {
        return $this->biometrics->groups();
    }

    /** @return Collection<int, BiometricCompany> */
    public function companies(): Collection
    {
        return $this->companies->all();
    }

    /** Record with company and linked HR employee, for the edit form. */
    public function forEdit(EmployeeBiometric $employee): EmployeeBiometric
    {
        return $this->biometrics->load($employee, ['company', 'hrEmployee']);
    }

    /** The employee profile: details plus the saved permanent schedule. */
    public function forProfile(EmployeeBiometric $employee): EmployeeBiometric
    {
        return $this->biometrics->load($employee, ['company', 'hrEmployee', 'permanentSchedule']);
    }

    /** @return list<array{value: string, label: string, hint: string}> */
    public function hrEmployeeOptions(EmployeeBiometric $employee): array
    {
        return $this->links->employeeOptions($employee);
    }

    /**
     * Pulls every CrossChex account and records the result in the payroll audit log.
     *
     * @return array{created: int, updated: int, skipped: int, merged: int}
     *
     * @throws Throwable when the sync fails (after logging it)
     */
    public function syncFromCrossChex(): array
    {
        try {
            $result = $this->sync->syncAllAccounts();
        } catch (Throwable $exception) {
            $this->audit->record(
                module: 'biometrics',
                action: 'employee_sync_failed',
                description: 'CrossChex employee master synchronization failed.',
                context: ['error' => $exception->getMessage()],
            );

            throw $exception;
        }

        $counts = [
            'created' => (int) ($result['created'] ?? 0),
            'updated' => (int) ($result['updated'] ?? 0),
            'skipped' => (int) ($result['skipped'] ?? 0),
            'merged' => (int) ($result['merged'] ?? 0),
        ];

        $this->audit->record(
            module: 'biometrics',
            action: 'employee_sync_completed',
            description: 'CrossChex employee master synchronization completed.',
            context: [
                'created' => $counts['created'],
                'updated' => $counts['updated'],
                'skipped' => $counts['skipped'],
                'merged_log_duplicates' => $counts['merged'],
            ],
        );

        return $counts;
    }

    /**
     * Saves the manual fields and the HR employee link. A record that is not active is never
     * included in payroll.
     *
     * @param  array<string, mixed>  $data  validated input, including hr_employee_id
     */
    public function update(EmployeeBiometric $employee, array $data): EmployeeBiometric
    {
        $hrEmployeeId = $data['hr_employee_id'] ?? null;
        unset($data['hr_employee_id']);

        return DB::transaction(function () use ($employee, $data, $hrEmployeeId): EmployeeBiometric {
            $this->updateManualFields($employee, $data);
            $this->links->assignEmployee($employee, $hrEmployeeId ? (int) $hrEmployeeId : null);

            return $employee->refresh();
        });
    }

    /** @param array<string, mixed> $data */
    private function updateManualFields(EmployeeBiometric $employee, array $data): void
    {
        $status = $this->identity->clean($data['employment_status'] ?? $employee->employment_status) ?: EmployeeBiometric::STATUS_ACTIVE;
        $isPayrollActive = $status === EmployeeBiometric::STATUS_ACTIVE
            && (bool) ($data['is_payroll_active'] ?? $employee->is_payroll_active);

        $payload = [
            'biometric_company_id' => array_key_exists('biometric_company_id', $data) ? $data['biometric_company_id'] : $employee->biometric_company_id,
            'display_employee_no' => $this->identity->clean($data['display_employee_no'] ?? $employee->display_employee_no),
            'display_name' => $this->identity->clean($data['display_name'] ?? $employee->display_name),
            'employment_status' => $status,
            'group_name' => $this->identity->clean($data['group_name'] ?? $employee->group_name),
            'is_payroll_active' => $isPayrollActive,
            'inactive_at' => $status === EmployeeBiometric::STATUS_ACTIVE ? null : ($employee->inactive_at ?? now('Asia/Manila')),
            'remarks' => $this->identity->clean($data['remarks'] ?? $employee->remarks),
        ];
        $payload['employee_identity_hash'] = $this->identity->identityHash(array_merge($employee->toArray(), $payload));

        $this->biometrics->update($employee, $payload);
    }
}
