<?php

namespace App\Http\Controllers\Biometrics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Biometrics\UpdateEmployeeBiometricRequest;
use App\Models\BiometricCompany;
use App\Models\EmployeeBiometric;
use App\Services\Biometrics\EmployeeBiometricService;
use App\Services\Biometrics\EmployeeBiometricSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class EmployeeBiometricController extends Controller
{
    public function __construct(
        protected EmployeeBiometricService $employeeBiometricService,
        protected EmployeeBiometricSyncService $employeeBiometricSyncService,
    ) {}

    public function index(Request $request): View
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

        return view('biometrics.employees.index', [
            'employeeBiometrics' => $employeeBiometrics,
            'companies' => $companies,
            'counts' => $this->employeeBiometricService->counts(),
            'groups' => $this->employeeBiometricService->groups(),
            'filters' => $filters,
        ]);
    }

    public function sync(): RedirectResponse
    {
        try {
            $result =
                $this->employeeBiometricSyncService
                    ->syncAllAccounts();

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
            report($exception);

            return to_route('biometrics.employees.index')
                ->withErrors([
                    'sync' => 'CrossChex synchronization failed. Check storage/logs/laravel.log for the exact production error.',
                ]);
        }
    }

    public function edit(
        EmployeeBiometric $employeeBiometric
    ): View {
        $companies = BiometricCompany::query()
            ->orderBy('name')
            ->get();

        return view('biometrics.employees.edit', [
            'employeeBiometric' => $employeeBiometric->load('company'),

            'companies' => $companies,

            'groups' => $this->employeeBiometricService->groups(),
        ]);
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
