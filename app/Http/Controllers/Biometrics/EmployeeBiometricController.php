<?php

declare(strict_types=1);

namespace App\Http\Controllers\Biometrics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Biometrics\UpdateEmployeeBiometricRequest;
use App\Models\BiometricCompany;
use App\Models\EmployeeBiometric;
use App\Services\Biometrics\EmployeeBiometricService;
use App\Services\Biometrics\EmployeeBiometricSyncService;
use App\Services\Payroll\PayrollAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class EmployeeBiometricController extends Controller
{
    public function __construct(
        protected EmployeeBiometricService $employeeBiometricService,
        protected EmployeeBiometricSyncService $employeeBiometricSyncService,
        protected PayrollAuditService $payrollAuditService,
    ) {}

    public function index(Request $request): Response
    {
        $filters = [
            'search' => trim(
                (string) $request->query('search')
            ),

            'employment_status' => trim(
                (string) $request->query('employment_status')
            ),

            'biometric_company_id' => trim(
                (string) $request->query('biometric_company_id')
            ),

            'group_name' => trim(
                (string) $request->query('group_name')
            ),

            'payroll_active' => trim(
                (string) $request->query('payroll_active')
            ),
        ];

        $employeeBiometrics =
            $this->employeeBiometricService->paginate($filters);

        $companies = BiometricCompany::query()
            ->orderBy('name')
            ->get();

        $counts = $this->employeeBiometricService->counts();
        $user = $request->user();

        return Inertia::render('biometrics/employees/index', [
            'employees' => $employeeBiometrics->through(fn (EmployeeBiometric $employee): array => $this->listRow($employee)),
            'companies' => $companies->map(fn (BiometricCompany $company): array => ['id' => $company->id, 'name' => $company->name])->values(),
            'counts' => [
                'total' => (int) ($counts['total'] ?? 0),
                'active' => (int) ($counts['active'] ?? 0),
                'payroll_active' => (int) ($counts['payroll_active'] ?? 0),
                'inactive' => (int) ($counts['inactive'] ?? 0),
                'without_company' => (int) ($counts['without_company'] ?? 0),
            ],
            'groups' => collect($this->employeeBiometricService->groups())->map(fn ($group): string => (string) $group)->values(),
            'filters' => $filters,
            'can' => [
                'sync' => (bool) $user?->can('biometrics.sync'),
                'edit' => (bool) $user?->can('biometrics.edit'),
                'createCompany' => (bool) $user?->can('biometrics.create'),
            ],
            'urls' => [
                'index' => route('biometrics.employees.index'),
                'sync' => route('biometrics.employees.sync'),
                'companyStore' => route('biometrics.companies.store'),
            ],
        ]);
    }

    public function sync(): RedirectResponse
    {
        try {
            $result =
                $this->employeeBiometricSyncService
                    ->syncAllAccounts();

            $this->payrollAuditService->record(
                module: 'biometrics',
                action: 'employee_sync_completed',
                description: 'CrossChex employee master synchronization completed.',
                context: [
                    'created' => (int) ($result['created'] ?? 0),
                    'updated' => (int) ($result['updated'] ?? 0),
                    'skipped' => (int) ($result['skipped'] ?? 0),
                    'merged_log_duplicates' => (int) ($result['merged'] ?? 0),
                ],
            );

            return to_route('biometrics.employees.index')
                ->with(
                    'success',
                    sprintf(
                        'CrossChex accounts synchronized successfully. Created: %d, Updated: %d, Skipped: %d, Merged log duplicates: %d.',
                        $result['created'],
                        $result['updated'],
                        $result['skipped'],
                        $result['merged']
                    )
                );
        } catch (Throwable $exception) {
            $this->payrollAuditService->record(
                module: 'biometrics',
                action: 'employee_sync_failed',
                description: 'CrossChex employee master synchronization failed.',
                context: [
                    'error' => $exception->getMessage(),
                ],
            );

            report($exception);

            return to_route('biometrics.employees.index')
                ->withErrors([
                    'sync' => 'CrossChex synchronization failed. Check storage/logs/laravel.log for the exact production error.',
                ]);
        }
    }

    public function edit(
        EmployeeBiometric $employeeBiometric
    ): Response {
        $companies = BiometricCompany::query()
            ->orderBy('name')
            ->get();

        $employeeBiometric->load('company');

        return Inertia::render('biometrics/employees/edit', [
            'employee' => [
                'id' => $employeeBiometric->id,
                'display_name' => $employeeBiometric->display_name,
                'display_employee_no' => $employeeBiometric->display_employee_no,
                'company_name' => $employeeBiometric->company?->name,
                'payroll_group_label' => $employeeBiometric->payroll_group_label,
                'legacy_id' => $employeeBiometric->legacy_biometric_employee_id
                    ?? $employeeBiometric->source_employee_id
                    ?? $employeeBiometric->source_crosschex_id
                    ?? $employeeBiometric->source_employee_no
                    ?? 'N/A',
                'source_employee_name' => $employeeBiometric->source_employee_name ?: 'N/A',
                'source_employee_no' => $employeeBiometric->source_employee_no ?: 'N/A',
                'source_crosschex_id' => $employeeBiometric->source_crosschex_id ?: 'N/A',
                'source_employee_id' => $employeeBiometric->source_employee_id ?: 'N/A',
                'crosschex_account' => $employeeBiometric->source_crosschex_account ?: 'N/A',
                'crosschex_account_name' => $employeeBiometric->source_crosschex_account_name ?: 'No account name',
                'device_name' => $employeeBiometric->device_name ?: 'N/A',
                'device_sn' => $employeeBiometric->device_sn ?: 'N/A',
                'last_check_time' => $employeeBiometric->last_check_time?->format('M d, Y h:i A') ?? 'N/A',
                'total_logs' => (int) ($employeeBiometric->total_logs ?? 0),
            ],
            'values' => [
                'biometric_company_id' => $employeeBiometric->biometric_company_id ? (string) $employeeBiometric->biometric_company_id : '',
                'group_name' => $employeeBiometric->group_name ? (string) $employeeBiometric->group_name : '',
                'employment_status' => $employeeBiometric->employment_status ?: EmployeeBiometric::STATUS_ACTIVE,
                'is_payroll_active' => (bool) ($employeeBiometric->is_payroll_active ?? true),
                'display_employee_no' => (string) ($employeeBiometric->display_employee_no ?? ''),
                'display_name' => (string) ($employeeBiometric->display_name ?? ''),
                'remarks' => (string) ($employeeBiometric->remarks ?? ''),
            ],
            'companies' => $companies->map(fn (BiometricCompany $company): array => ['id' => $company->id, 'name' => $company->name])->values(),
            'groupOptions' => [
                (string) EmployeeBiometric::PAYROLL_GROUP_MIRASOL => 'Mirasol / Balintawak Payroll',
                (string) EmployeeBiometric::PAYROLL_GROUP_GONZALES => 'Gonzales Payroll',
            ],
            'can' => ['update' => (bool) auth()->user()?->can('biometrics.update')],
            'urls' => [
                'index' => route('biometrics.employees.index'),
                'update' => route('biometrics.employees.update', $employeeBiometric),
            ],
        ]);
    }

    private function listRow(EmployeeBiometric $employee): array
    {
        $isActive = $employee->employment_status === EmployeeBiometric::STATUS_ACTIVE;

        return [
            'id' => $employee->id,
            'display_name' => $employee->payroll_display_name,
            'display_no' => $employee->display_employee_no ?: 'N/A',
            'source_no' => $employee->source_employee_no ?: 'N/A',
            'group_label' => match ((int) $employee->group_name) {
                EmployeeBiometric::PAYROLL_GROUP_MIRASOL => 'Mirasol / Balintawak Payroll',
                EmployeeBiometric::PAYROLL_GROUP_GONZALES => 'Gonzales Payroll',
                default => null,
            },
            'company' => $employee->company?->name,
            'active' => $isActive,
            'payroll_included' => $isActive && (bool) ($employee->is_payroll_active ?? true),
            'device_name' => $employee->device_name ?: 'N/A',
            'device_sn' => $employee->device_sn ?: 'N/A',
            'last_check_date' => $employee->last_check_time?->format('M d, Y'),
            'last_check_time' => $employee->last_check_time?->format('h:i A'),
            'total_logs' => (int) ($employee->total_logs ?? 0),
            'edit_url' => route('biometrics.employees.edit', $employee),
        ];
    }

    public function update(
        UpdateEmployeeBiometricRequest $request,
        EmployeeBiometric $employeeBiometric
    ): RedirectResponse {
        $this->employeeBiometricService->updateManualFields(
            $employeeBiometric,
            $request->validated()
        );

        return to_route('biometrics.employees.index')
            ->with(
                'success',
                'Biometric employee record updated successfully.'
            );
    }
}
