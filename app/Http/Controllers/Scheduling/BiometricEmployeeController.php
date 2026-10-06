<?php

declare(strict_types=1);

namespace App\Http\Controllers\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Scheduling\Concerns\BuildsEmployeeRateForm;
use App\Http\Requests\Scheduling\UpdateEmployeeBiometricRequest;
use App\Http\Resources\Scheduling\BiometricEmployeeFormResource;
use App\Http\Resources\Scheduling\BiometricEmployeeRowResource;
use App\Http\Resources\Scheduling\EmployeeRateFormResource;
use App\Http\Resources\Scheduling\WorkScheduleRowResource;
use App\Models\BiometricCompany;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\PayrollEmployeeSalary;
use App\Services\Payroll\PayrollGroupAccessService;
use App\Services\Scheduling\BiometricEmployeeService;
use App\Services\Scheduling\EmployeeRateService;
use App\Services\Scheduling\WorkScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Scheduling & Rates → Employees (the biometric people payroll runs on). The employee
 * profile (show) puts Details, Work schedule and Rates of one person in one place.
 */
final class BiometricEmployeeController extends Controller
{
    use BuildsEmployeeRateForm;

    public function __construct(
        private readonly BiometricEmployeeService $employees,
        private readonly WorkScheduleService $schedules,
        private readonly EmployeeRateService $rates,
        private readonly PayrollGroupAccessService $groups,
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
                'bulkSchedule' => (bool) $user?->can('payroll-plotting.view'),
                'ratesList' => (bool) $user?->can('employee-salaries.view'),
            ],
            'urls' => [
                'index' => route('biometrics.employees.index'),
                'sync' => route('biometrics.employees.sync'),
                'companyStore' => route('biometrics.companies.store'),
                'bulkSchedule' => route('payroll-plotting.index'),
                'ratesList' => route('payroll-employee-salaries.index'),
            ],
        ]);
    }

    /**
     * One employee: Details, Work schedule and Rates. Each tab is only filled when the user
     * holds that page's permission; saving uses the same routes as the separate pages.
     */
    public function show(Request $request, EmployeeBiometric $employeeBiometric): Response
    {
        $user = $request->user();
        $employee = $this->employees->forProfile($employeeBiometric);
        $schedule = $employee->permanentSchedule;
        $salary = $this->canSeeRates($request, $employee) ? $this->rates->forEmployee($employee) : null;
        $row = BiometricEmployeeRowResource::make($employee)->resolve($request);

        return Inertia::render('biometrics/employees/show', [
            'employee' => $row + [
                'remarks' => $employee->remarks,
                'last_check' => $employee->last_check_time?->format('M d, Y h:i A'),
            ],
            'checklist' => array_values(array_filter([
                ['key' => 'details', 'label' => 'Company tag', 'done' => $employee->biometric_company_id !== null],
                ['key' => 'details', 'label' => 'Payroll group', 'done' => filled($employee->group_name)],
                ['key' => 'details', 'label' => 'Linked HR employee', 'done' => $employee->hrEmployee !== null],
                $user->can('payroll-plotting.view') ? ['key' => 'schedule', 'label' => 'Work schedule', 'done' => $schedule !== null] : null,
                $this->canSeeRates($request, $employee) ? ['key' => 'rates', 'label' => 'Employee rate', 'done' => $salary !== null] : null,
            ])),
            'details' => $user->can('biometrics.update') ? [
                ...BiometricEmployeeFormResource::make($employee)->resolve($request),
                'hrEmployees' => $this->employees->hrEmployeeOptions($employee),
                'companies' => $this->companyOptions(),
                'groupOptions' => collect(EmployeeBiometric::GROUP_LABELS)->mapWithKeys(fn (string $label, int $group): array => [(string) $group => $label])->all(),
                'can' => ['update' => true],
                'urls' => [
                    'index' => route('biometrics.employees.index'),
                    'update' => route('biometrics.employees.update', $employee),
                ],
            ] : null,
            'schedule' => $user->can('payroll-plotting.view') ? [
                'row' => (new WorkScheduleRowResource($employee, $this->schedules->identity($employee)))->resolve($request),
                'workdayRules' => $this->schedules->workdayRules(),
                'weekdays' => EmployeePlottingSchedule::WEEKDAYS,
                'canUpdate' => $user->can('payroll-plotting.update'),
                'payrollActive' => $employee->employment_status === EmployeeBiometric::STATUS_ACTIVE && (bool) ($employee->is_payroll_active ?? true),
                'urls' => ['save' => route('payroll-plotting.save')],
            ] : null,
            'rates' => $this->ratesTab($request, $employee, $salary),
            'urls' => [
                'index' => route('biometrics.employees.index'),
                'bulkSchedule' => $user->can('payroll-plotting.view') ? route('payroll-plotting.index', ['search' => $employee->payroll_display_name]) : null,
                'ratesList' => $user->can('employee-salaries.view') ? route('payroll-employee-salaries.index') : null,
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

        if ($request->integer('return_profile') === (int) $employeeBiometric->id) {
            return to_route('biometrics.employees.show', $employeeBiometric)->with('success', 'Biometric employee record updated successfully.');
        }

        return to_route('biometrics.employees.index')->with('success', 'Biometric employee record updated successfully.');
    }

    private function canSeeRates(Request $request, EmployeeBiometric $employee): bool
    {
        return $request->user()->can('employee-salaries.view') && $this->groups->allows($employee->group_name);
    }

    /**
     * The Rates tab: the Employee Rates form (edit, or create for this person) or why it is not shown.
     *
     * @return array<string, mixed>|null
     */
    private function ratesTab(Request $request, EmployeeBiometric $employee, ?PayrollEmployeeSalary $salary): ?array
    {
        $user = $request->user();

        if (! $user->can('employee-salaries.view')) {
            return null;
        }

        if (! $this->groups->allows($employee->group_name)) {
            return ['mode' => 'unavailable', 'message' => 'This employee is not in one of your payroll groups, so the rate is hidden.'];
        }

        $person = $this->rates->person($employee);
        $hours = ['paid_hours' => $person['paid_work_hours'], 'label' => $person['workday_label']];
        $summary = $salary ? [
            'rate_type' => ucfirst((string) $salary->rate_type),
            'basic_salary' => (float) $salary->basic_salary,
            'allowance' => (float) ($salary->allowance ?? 0),
            'paid_day_off' => (bool) ($salary->paid_day_off ?? true),
            'is_active' => (bool) $salary->is_active,
        ] : null;

        if ($salary) {
            return [
                'mode' => $user->can('employee-salaries.update') ? 'edit' : 'view',
                'summary' => $summary,
                'form' => $user->can('employee-salaries.update')
                    ? $this->rateFormProps($request, $salary, [$person], EmployeeRateFormResource::make($salary)->resolve($request), $hours)
                    : null,
            ];
        }

        $payrollActive = $employee->employment_status === EmployeeBiometric::STATUS_ACTIVE && (bool) ($employee->is_payroll_active ?? true);

        if (! $user->can('employee-salaries.create') || ! $payrollActive) {
            return [
                'mode' => 'unavailable',
                'message' => $payrollActive
                    ? 'No rate yet. You do not have permission to add one.'
                    : 'No rate yet. A rate can be added once the employee is Active with Payroll inclusion on (Details tab).',
            ];
        }

        $values = array_merge(EmployeeRateFormResource::make(null)->resolve($request), [
            'employee_biometric_id' => $person['employee_biometric_id'],
            'employee_no' => (string) ($person['employee_no'] ?? ''),
            'employee_name' => (string) ($person['employee_name'] ?? ''),
            'crosschex_id' => (string) ($person['crosschex_id'] ?? ''),
            'biometric_employee_id' => (string) ($person['biometric_employee_id'] ?? ''),
        ]);

        return [
            'mode' => 'create',
            'summary' => null,
            'form' => $this->rateFormProps($request, null, [$person], $values, $hours),
        ];
    }

    /** @return Collection<int, array{id: int, name: string}> */
    private function companyOptions(): Collection
    {
        return $this->employees->companies()
            ->map(fn (BiometricCompany $company): array => ['id' => $company->id, 'name' => $company->name])
            ->values();
    }
}
