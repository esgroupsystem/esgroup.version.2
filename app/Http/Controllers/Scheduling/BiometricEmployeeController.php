<?php

declare(strict_types=1);

namespace App\Http\Controllers\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scheduling\UpdateEmployeeBiometricRequest;
use App\Http\Resources\Scheduling\BiometricEmployeeFormResource;
use App\Http\Resources\Scheduling\BiometricEmployeeRowResource;
use App\Models\BiometricCompany;
use App\Models\EmployeeBiometric;
use App\Services\Scheduling\BiometricEmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Scheduling & Rates → Employees (the biometric people payroll runs on).
 */
final class BiometricEmployeeController extends Controller
{
    public function __construct(
        private readonly BiometricEmployeeService $employees,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $this->employees->filters($request->query());
        $user = $request->user();

        return Inertia::render('biometrics/employees/index', [
            'employees' => $this->employees->paginate($filters)
                ->through(fn (EmployeeBiometric $employee): array => BiometricEmployeeRowResource::make($employee)->resolve($request)),
            'companies' => $this->companyOptions(),
            'counts' => $this->employees->counts(),
            'groups' => $this->employees->groups()->values(),
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
            $result = $this->employees->syncFromCrossChex();
        } catch (Throwable $exception) {
            report($exception);

            return to_route('biometrics.employees.index')->withErrors([
                'sync' => 'CrossChex synchronization failed. Check storage/logs/laravel.log for the exact production error.',
            ]);
        }

        return to_route('biometrics.employees.index')->with('success', sprintf(
            'CrossChex accounts synchronized successfully. Created: %d, Updated: %d, Skipped: %d, Merged log duplicates: %d.',
            $result['created'],
            $result['updated'],
            $result['skipped'],
            $result['merged'],
        ));
    }

    public function edit(Request $request, EmployeeBiometric $employeeBiometric): Response
    {
        $employee = $this->employees->forEdit($employeeBiometric);

        return Inertia::render('biometrics/employees/edit', [
            ...BiometricEmployeeFormResource::make($employee)->resolve($request),
            'hrEmployees' => $this->employees->hrEmployeeOptions($employee),
            'companies' => $this->companyOptions(),
            'groupOptions' => collect(EmployeeBiometric::GROUP_LABELS)->mapWithKeys(fn (string $label, int $group): array => [(string) $group => $label])->all(),
            'can' => ['update' => (bool) $request->user()?->can('biometrics.update')],
            'urls' => [
                'index' => route('biometrics.employees.index'),
                'update' => route('biometrics.employees.update', $employee),
            ],
        ]);
    }

    public function update(UpdateEmployeeBiometricRequest $request, EmployeeBiometric $employeeBiometric): RedirectResponse
    {
        $this->employees->update($employeeBiometric, $request->validated());

        return to_route('biometrics.employees.index')->with('success', 'Biometric employee record updated successfully.');
    }

    /** @return Collection<int, array{id: int, name: string}> */
    private function companyOptions(): Collection
    {
        return $this->employees->companies()
            ->map(fn (BiometricCompany $company): array => ['id' => $company->id, 'name' => $company->name])
            ->values();
    }
}
