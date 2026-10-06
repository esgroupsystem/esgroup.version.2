<?php

declare(strict_types=1);

namespace App\Http\Controllers\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Scheduling\Concerns\BuildsEmployeeRateForm;
use App\Http\Requests\Scheduling\EmployeeRateRequest;
use App\Http\Resources\Scheduling\EmployeeRateFormResource;
use App\Http\Resources\Scheduling\EmployeeRateRowResource;
use App\Models\PayrollEmployeeSalary;
use App\Services\Scheduling\EmployeeRateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

/**
 * Scheduling & Rates → Employee Rates. The rows a user sees are limited to their payroll
 * groups (session "payroll_allowed_groups", set by the payroll.group middleware).
 */
final class EmployeeRateController extends Controller
{
    use BuildsEmployeeRateForm;

    public function __construct(
        private readonly EmployeeRateService $rates,
    ) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $group = trim((string) $request->input('group_name', ''));
        $employmentStatus = trim((string) $request->input('employment_status', ''));
        $user = $request->user();

        return Inertia::render('payroll/employee-salaries/index', [
            'salaries' => $this->rates->paginate($search, $group, $employmentStatus, $this->allowedGroups())
                ->through(fn (PayrollEmployeeSalary $salary): array => EmployeeRateRowResource::make($salary)->resolve($request)),
            'filters' => ['search' => $search, 'group_name' => $group, 'employment_status' => $employmentStatus],
            'groups' => $this->rates->groups()->values(),
            'can' => [
                'create' => $user->can('employee-salaries.create'),
                'update' => $user->can('employee-salaries.update'),
                'delete' => $user->can('employee-salaries.delete'),
            ],
            'urls' => [
                'index' => route('payroll-employee-salaries.index'),
                'create' => route('payroll-employee-salaries.create'),
                'sync' => route('payroll-employee-salaries.sync'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->form($request, null);
    }

    public function store(EmployeeRateRequest $request): RedirectResponse
    {
        $salary = $this->rates->create($request->validated());

        if ($request->integer('return_profile') === (int) $salary->employee_biometric_id) {
            return to_route('biometrics.employees.show', $salary->employee_biometric_id)->with('success', 'Salary record created successfully.');
        }

        return redirect()->route('payroll-employee-salaries.index')->with('success', 'Salary record created successfully.');
    }

    public function edit(Request $request, PayrollEmployeeSalary $payrollEmployeeSalary): Response
    {
        return $this->form($request, $this->rates->forEdit($payrollEmployeeSalary));
    }

    public function update(EmployeeRateRequest $request, PayrollEmployeeSalary $payrollEmployeeSalary): RedirectResponse
    {
        $this->rates->update($payrollEmployeeSalary, $request->validated());

        if ($payrollEmployeeSalary->employee_biometric_id && $request->integer('return_profile') === (int) $payrollEmployeeSalary->employee_biometric_id) {
            return to_route('biometrics.employees.show', $payrollEmployeeSalary->employee_biometric_id)->with('success', 'Salary record updated successfully.');
        }

        return redirect()->route('payroll-employee-salaries.index')->with('success', 'Salary record updated successfully.');
    }

    public function destroy(PayrollEmployeeSalary $payrollEmployeeSalary): RedirectResponse
    {
        $this->rates->delete($payrollEmployeeSalary);

        return redirect()->route('payroll-employee-salaries.index')->with('success', 'Salary record deleted successfully.');
    }

    public function syncFromBiometrics(Request $request): RedirectResponse
    {
        try {
            $result = $this->rates->syncFromBiometrics();
        } catch (Throwable $exception) {
            Log::error('Payroll biometric salary sync failed.', ['user_id' => $request->user()?->getKey(), 'exception' => $exception]);

            return redirect()->route('payroll-employee-salaries.index')->with('error', 'Biometrics sync failed. Check the application log for details.');
        }

        if ($result === null) {
            return redirect()->route('payroll-employee-salaries.index')->with('warning', 'A biometric salary sync is already running.');
        }

        return redirect()
            ->route('payroll-employee-salaries.index')
            ->with('success', "Biometrics sync completed. {$result['inserted']} added, {$result['updated']} updated, {$result['skipped']} skipped.");
    }

    private function form(Request $request, ?PayrollEmployeeSalary $salary): Response
    {
        return Inertia::render('payroll/employee-salaries/form', $this->rateFormProps(
            $request,
            $salary,
            $this->rates->people($this->allowedGroups()),
            EmployeeRateFormResource::make($salary)->resolve($request),
        ));
    }

    /** @return string|list<int|string>|null */
    private function allowedGroups(): string|array|null
    {
        return session('payroll_allowed_groups');
    }
}
