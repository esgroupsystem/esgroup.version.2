<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\PayrollEmployeeSalary;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use App\Services\Payroll\PayrollDeductionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class PayrollEmployeeSalaryController extends Controller
{
    public function __construct(
        private readonly PayrollDeductionService $deductionService,
        private readonly EmployeeBiometricIdentityService $identityService
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->search);
        $groupName = trim((string) $request->group_name);
        $allowedGroups = session('payroll_allowed_groups');

        $salaries = PayrollEmployeeSalary::query()
            ->with(['otherDeductions', 'employeeBiometric'])
            ->when(
                $allowedGroups !== 'all',
                function ($query) use ($allowedGroups) {

                    if (empty($allowedGroups)) {

                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->whereHas(
                        'employeeBiometric',
                        function ($employeeQuery) use ($allowedGroups) {

                            $employeeQuery->whereIn(
                                'group_name',
                                $allowedGroups
                            );

                        }
                    );

                }
            )
            ->whereIn('id', function ($query): void {
                $query->selectRaw('COALESCE(MAX(CASE WHEN basic_salary > 0 THEN id END), MIN(id))')
                    ->from('payroll_employee_salaries')
                    ->groupBy('employee_biometric_id');
            })
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('employee_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%")
                        ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                        ->orWhere('crosschex_id', 'like', "%{$search}%")
                        ->orWhereHas('employeeBiometric', function ($employeeQuery) use ($search): void {
                            $employeeQuery
                                ->where('display_name', 'like', "%{$search}%")
                                ->orWhere('display_employee_no', 'like', "%{$search}%")
                                ->orWhere('group_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($groupName !== '', function ($query) use ($groupName): void {
                $query->whereHas('employeeBiometric', fn ($employeeQuery) => $employeeQuery->where('group_name', $groupName));
            })
            ->orderBy('employee_name')
            ->orderBy('employee_no')
            ->paginate(15)
            ->withQueryString();

        $salaries->getCollection()->transform(function (PayrollEmployeeSalary $salary) {
            $salary->payroll_preview = $this->deductionService->salaryPreview($salary);

            return $salary;
        });

        $groups = EmployeeBiometric::query()
            ->whereNotNull('group_name')
            ->where('group_name', '!=', '')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name');

        return view('payroll.employee_salaries.index', compact('salaries', 'search', 'groupName', 'groups'));
    }

    public function create(): View
    {
        $people = $this->biometricPeople();

        return view('payroll.employee_salaries.create', compact('people'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $employee = EmployeeBiometric::query()
            ->payrollActive()
            ->findOrFail((int) $validated['employee_biometric_id']);

        $computed = $this->deductionService->computeRates(
            (float) $validated['basic_salary'],
            $validated['rate_type'],
            $this->paidHoursForEmployee($employee->id)
        );

        DB::transaction(function () use ($validated, $computed, $employee): void {
            $salary = PayrollEmployeeSalary::create($this->payload($validated, $computed, $employee));

            $this->syncOtherDeductions($salary, $validated['other_deductions'] ?? []);
        });

        return redirect()
            ->route('payroll-employee-salaries.index')
            ->with('success', 'Salary record created successfully.');
    }

    public function edit(PayrollEmployeeSalary $payrollEmployeeSalary): View
    {
        $payrollEmployeeSalary->load([
            'otherDeductions',
            'employeeBiometric.plottingSchedules' => fn ($query) => $query
                ->whereNull('work_date')
                ->latest('id'),
        ]);

        return view('payroll.employee_salaries.edit', [
            'salary' => $payrollEmployeeSalary,
            'people' => $this->biometricPeople(),
        ]);
    }

    public function update(Request $request, PayrollEmployeeSalary $payrollEmployeeSalary): RedirectResponse
    {
        $validated = $request->validate($this->rules($payrollEmployeeSalary));

        $employee = EmployeeBiometric::query()
            ->findOrFail((int) ($validated['employee_biometric_id'] ?? $payrollEmployeeSalary->employee_biometric_id));

        $computed = $this->deductionService->computeRates(
            (float) $validated['basic_salary'],
            $validated['rate_type'],
            $this->paidHoursForEmployee($employee->id)
        );

        DB::transaction(function () use ($payrollEmployeeSalary, $validated, $computed, $employee): void {
            $payrollEmployeeSalary->update($this->payload($validated, $computed, $employee));

            $this->syncOtherDeductions($payrollEmployeeSalary, $validated['other_deductions'] ?? []);
        });

        return redirect()
            ->route('payroll-employee-salaries.index')
            ->with('success', 'Salary record updated successfully.');
    }

    public function destroy(PayrollEmployeeSalary $payrollEmployeeSalary): RedirectResponse
    {
        $payrollEmployeeSalary->delete();

        return redirect()
            ->route('payroll-employee-salaries.index')
            ->with('success', 'Salary record deleted successfully.');
    }

    public function syncFromBiometrics(): RedirectResponse
    {
        $lock = Cache::lock(
            'payroll:employee-salaries:sync-from-biometrics',
            300
        );

        if (! $lock->get()) {
            return redirect()
                ->route('payroll-employee-salaries.index')
                ->with('warning', 'A biometric salary sync is already running.');
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        try {
            EmployeeBiometric::query()
                ->payrollActive()
                ->orderBy('id')
                ->chunkById(200, function ($people) use (
                    &$inserted,
                    &$updated,
                    &$skipped
                ): void {
                    [$chunkInserted, $chunkUpdated, $chunkSkipped] = DB::transaction(
                        function () use ($people): array {
                            $localInserted = 0;
                            $localUpdated = 0;
                            $localSkipped = 0;

                            foreach ($people as $employee) {
                                $snapshot = $this->identityService->snapshot($employee);
                                $employeeName = $this->cleanText(
                                    $snapshot['employee_name'] ?? null
                                );

                                if (
                                    $employeeName === null
                                    || strcasecmp($employeeName, 'Unknown Employee') === 0
                                ) {
                                    $localSkipped++;

                                    continue;
                                }

                                /*
                                 * employee_biometric_id is the canonical local identity.
                                 * biometric_employee_id is only a legacy/source value and
                                 * may repeat across different CrossChex accounts.
                                 */
                                $salary = PayrollEmployeeSalary::query()
                                    ->where('employee_biometric_id', $employee->id)
                                    ->lockForUpdate()
                                    ->latest('id')
                                    ->first();

                                if (! $salary) {
                                    PayrollEmployeeSalary::create(
                                        $this->defaultSalaryPayload($employee, $snapshot)
                                    );

                                    $localInserted++;

                                    continue;
                                }

                                $salary->update([
                                    'biometric_employee_id' => $this->cleanText(
                                        $snapshot['biometric_employee_id'] ?? null
                                    ) ?: $salary->biometric_employee_id,
                                    'employee_no' => $this->cleanText(
                                        $snapshot['employee_no'] ?? null
                                    ) ?: $salary->employee_no,
                                    'employee_name' => $employeeName,
                                    'crosschex_id' => $this->cleanText(
                                        $snapshot['crosschex_id'] ?? null
                                    ) ?: $salary->crosschex_id,
                                    'is_active' => (bool) $employee->is_payroll_active,
                                ]);

                                $localUpdated++;
                            }

                            return [
                                $localInserted,
                                $localUpdated,
                                $localSkipped,
                            ];
                        },
                        3
                    );

                    $inserted += $chunkInserted;
                    $updated += $chunkUpdated;
                    $skipped += $chunkSkipped;
                });
        } catch (Throwable $exception) {
            Log::error('Payroll biometric salary sync failed.', [
                'user_id' => auth()->id(),
                'exception' => $exception,
            ]);

            return redirect()
                ->route('payroll-employee-salaries.index')
                ->with(
                    'error',
                    'Biometrics sync failed. Check the application log for details.'
                );
        } finally {
            $lock->release();
        }

        return redirect()
            ->route('payroll-employee-salaries.index')
            ->with(
                'success',
                "Biometrics sync completed. {$inserted} added, {$updated} updated, {$skipped} skipped."
            );
    }

    private function biometricPeople()
    {
        $allowedGroups = session('payroll_allowed_groups');

        return EmployeeBiometric::query()
            ->with([
                'plottingSchedules' => fn ($query) => $query
                    ->whereNull('work_date')
                    ->latest('id'),
            ])
            ->payrollActive()
            ->when(
                $allowedGroups !== 'all',
                function ($query) use ($allowedGroups) {
                    if (empty($allowedGroups)) {
                        $query->whereRaw('1 = 0');

                        return;
                    }
                    $query->whereIn(
                        'group_name',
                        $allowedGroups
                    );
                }
            )
            ->orderBy('group_name')
            ->orderByRaw("COALESCE(NULLIF(display_name, ''), NULLIF(source_employee_name, ''), NULLIF(source_crosschex_account_name, '')) ASC")
            ->get()
            ->map(function (EmployeeBiometric $employee) {
                $snapshot = $this->identityService->snapshot($employee);
                $schedule = $employee->plottingSchedules->first();

                return (object) [
                    'employee_biometric_id' => $employee->id,
                    'biometric_employee_id' => $snapshot['biometric_employee_id'],
                    'employee_no' => $snapshot['employee_no'],
                    'employee_name' => $snapshot['employee_name'],
                    'crosschex_id' => $snapshot['crosschex_id'],
                    'group_name' => $employee->group_name,
                    'last_check_time' => $employee->last_check_time,
                    'total_logs' => $employee->total_logs,
                    'workday_type' => $schedule?->resolvedWorkdayType()->value ?? 'eight_hours',
                    'paid_work_hours' => $schedule?->paidWorkHours() ?? 8.0,
                    'workday_label' => $schedule?->resolvedWorkdayType()->shortLabel() ?? '8 hrs + 1 hr lunch',
                ];
            })
            ->values();
    }

    private function paidHoursForEmployee(int $employeeBiometricId): float
    {
        $schedule = EmployeePlottingSchedule::query()
            ->where('employee_biometric_id', $employeeBiometricId)
            ->whereNull('work_date')
            ->latest('id')
            ->first();

        return $schedule?->paidWorkHours()
            ?? max(1, (float) config('payroll.salary_rate.paid_hours_per_day', 8));
    }

    private function rules(?PayrollEmployeeSalary $salary = null): array
    {
        $scheduleRule = Rule::in([
            'none',
            'first_cutoff',
            'second_cutoff',
            'every_cutoff',
        ]);

        $ignoreId = $salary?->id;

        return [
            'employee_biometric_id' => [
                'required',
                'integer',
                'exists:employee_biometrics,id',
                Rule::unique('payroll_employee_salaries', 'employee_biometric_id')->ignore($ignoreId),
            ],

            'employee_no' => ['nullable', 'string', 'max:255'],
            'employee_name' => ['required', 'string', 'max:255'],
            'crosschex_id' => ['nullable', 'string', 'max:255'],
            'biometric_employee_id' => ['nullable', 'string', 'max:255'],

            'rate_type' => ['required', Rule::in(['daily', 'monthly'])],
            'basic_salary' => ['required', 'numeric', 'min:0'],

            'allowance' => ['nullable', 'numeric', 'min:0'],
            'allowance_release_schedule' => ['required', $scheduleRule],
            'sim_load_allowance' => ['nullable', 'numeric', 'min:0'],
            'sim_load_release_schedule' => ['required', $scheduleRule],

            'sss_contribution_cutoff' => ['required', $scheduleRule],
            'pagibig_contribution_cutoff' => ['required', $scheduleRule],
            'philhealth_contribution_cutoff' => ['required', $scheduleRule],

            'sss_loan_total_amount' => ['nullable', 'numeric', 'min:0'],
            'sss_loan_payment_amount' => ['nullable', 'numeric', 'min:0'],
            'sss_loan_deduction_schedule' => ['required', $scheduleRule],
            'sss_loan_start_date' => ['nullable', 'date'],

            'pagibig_loan_total_amount' => ['nullable', 'numeric', 'min:0'],
            'pagibig_loan_payment_amount' => ['nullable', 'numeric', 'min:0'],
            'pagibig_loan_deduction_schedule' => ['required', $scheduleRule],
            'pagibig_loan_start_date' => ['nullable', 'date'],

            'philhealth_loan_total_amount' => ['nullable', 'numeric', 'min:0'],
            'philhealth_loan_payment_amount' => ['nullable', 'numeric', 'min:0'],
            'philhealth_loan_deduction_schedule' => ['required', $scheduleRule],
            'philhealth_loan_start_date' => ['nullable', 'date'],

            'cash_advance_total_amount' => ['nullable', 'numeric', 'min:0'],
            'cash_advance_payment_amount' => ['nullable', 'numeric', 'min:0'],
            'cash_advance_deduction_schedule' => ['required', $scheduleRule],
            'cash_advance_start_date' => ['nullable', 'date'],

            'other_loan_total_amount' => ['nullable', 'numeric', 'min:0'],
            'other_loan_payment_amount' => ['nullable', 'numeric', 'min:0'],
            'other_loan_deduction_schedule' => ['required', $scheduleRule],
            'other_loan_start_date' => ['nullable', 'date'],

            'other_deductions' => ['nullable', 'array', 'max:30'],
            'other_deductions.*.name' => ['nullable', 'string', 'max:255'],
            'other_deductions.*.total_amount' => ['nullable', 'numeric', 'min:0'],
            'other_deductions.*.payment_amount' => ['nullable', 'numeric', 'min:0'],
            'other_deductions.*.deduction_schedule' => ['nullable', $scheduleRule],
            'other_deductions.*.start_date' => ['nullable', 'date'],
            'other_deductions.*.remarks' => ['nullable', 'string', 'max:1000'],

            'is_active' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    private function payload(array $validated, array $computed, EmployeeBiometric $employee): array
    {
        $snapshot = $this->identityService->snapshot($employee);

        return [
            'employee_biometric_id' => $employee->id,
            'employee_no' => $snapshot['employee_no'],
            'employee_name' => $this->cleanText($validated['employee_name'] ?? null)
                ?: $snapshot['employee_name'],
            'crosschex_id' => $snapshot['crosschex_id'],
            'biometric_employee_id' => $snapshot['biometric_employee_id'],

            'rate_type' => $validated['rate_type'],
            'basic_salary' => $validated['basic_salary'],

            'allowance' => $validated['allowance'] ?? 0,
            'allowance_release_schedule' => $validated['allowance_release_schedule'],
            'sim_load_allowance' => $validated['sim_load_allowance'] ?? 0,
            'sim_load_release_schedule' => $validated['sim_load_release_schedule'],

            'sss_contribution_cutoff' => $validated['sss_contribution_cutoff'],
            'pagibig_contribution_cutoff' => $validated['pagibig_contribution_cutoff'],
            'philhealth_contribution_cutoff' => $validated['philhealth_contribution_cutoff'],

            'ot_rate_per_hour' => $computed['hourly_rate'],
            'late_deduction_per_minute' => $computed['per_minute_rate'],
            'undertime_deduction_per_minute' => $computed['per_minute_rate'],
            'absent_deduction_per_day' => $computed['daily_rate'],

            'sss_loan_total_amount' => $validated['sss_loan_total_amount'] ?? 0,
            'sss_loan_payment_amount' => $validated['sss_loan_payment_amount'] ?? 0,
            'sss_loan_deduction_schedule' => $validated['sss_loan_deduction_schedule'],
            'sss_loan_start_date' => $validated['sss_loan_start_date'] ?? null,

            'pagibig_loan_total_amount' => $validated['pagibig_loan_total_amount'] ?? 0,
            'pagibig_loan_payment_amount' => $validated['pagibig_loan_payment_amount'] ?? 0,
            'pagibig_loan_deduction_schedule' => $validated['pagibig_loan_deduction_schedule'],
            'pagibig_loan_start_date' => $validated['pagibig_loan_start_date'] ?? null,

            'philhealth_loan_total_amount' => $validated['philhealth_loan_total_amount'] ?? 0,
            'philhealth_loan_payment_amount' => $validated['philhealth_loan_payment_amount'] ?? 0,
            'philhealth_loan_deduction_schedule' => $validated['philhealth_loan_deduction_schedule'],
            'philhealth_loan_start_date' => $validated['philhealth_loan_start_date'] ?? null,

            'cash_advance_total_amount' => $validated['cash_advance_total_amount'] ?? 0,
            'cash_advance_payment_amount' => $validated['cash_advance_payment_amount'] ?? 0,
            'cash_advance_deduction_schedule' => $validated['cash_advance_deduction_schedule'],
            'cash_advance_start_date' => $validated['cash_advance_start_date'] ?? null,

            'other_loan_total_amount' => $validated['other_loan_total_amount'] ?? 0,
            'other_loan_payment_amount' => $validated['other_loan_payment_amount'] ?? 0,
            'other_loan_deduction_schedule' => $validated['other_loan_deduction_schedule'],
            'other_loan_start_date' => $validated['other_loan_start_date'] ?? null,

            'sss_loan' => $validated['sss_loan_payment_amount'] ?? 0,
            'pagibig_loan' => $validated['pagibig_loan_payment_amount'] ?? 0,
            'vale' => $validated['cash_advance_payment_amount'] ?? 0,
            'other_loans' => round(
                (float) ($validated['other_loan_payment_amount'] ?? 0)
                + $this->otherDeductionsPaymentTotal($validated['other_deductions'] ?? []),
                2
            ),

            'is_active' => (bool) ($validated['is_active'] ?? true),
            'remarks' => $validated['remarks'] ?? null,
        ];
    }

    private function defaultSalaryPayload(EmployeeBiometric $employee, array $snapshot): array
    {
        return [
            'employee_biometric_id' => $employee->id,
            'biometric_employee_id' => $snapshot['biometric_employee_id'],
            'employee_no' => $snapshot['employee_no'],
            'employee_name' => $snapshot['employee_name'],
            'crosschex_id' => $snapshot['crosschex_id'],

            'rate_type' => 'daily',
            'basic_salary' => 0,
            'allowance' => 0,
            'allowance_release_schedule' => 'every_cutoff',
            'sim_load_allowance' => 0,
            'sim_load_release_schedule' => 'every_cutoff',

            'sss_contribution_cutoff' => 'first_cutoff',
            'pagibig_contribution_cutoff' => 'second_cutoff',
            'philhealth_contribution_cutoff' => 'second_cutoff',

            'ot_rate_per_hour' => 0,
            'late_deduction_per_minute' => 0,
            'undertime_deduction_per_minute' => 0,
            'absent_deduction_per_day' => 0,

            'sss_loan' => 0,
            'pagibig_loan' => 0,
            'vale' => 0,
            'other_loans' => 0,

            'sss_loan_total_amount' => 0,
            'sss_loan_payment_amount' => 0,
            'sss_loan_deduction_schedule' => 'none',
            'sss_loan_start_date' => null,

            'pagibig_loan_total_amount' => 0,
            'pagibig_loan_payment_amount' => 0,
            'pagibig_loan_deduction_schedule' => 'none',
            'pagibig_loan_start_date' => null,

            'philhealth_loan_total_amount' => 0,
            'philhealth_loan_payment_amount' => 0,
            'philhealth_loan_deduction_schedule' => 'none',
            'philhealth_loan_start_date' => null,

            'cash_advance_total_amount' => 0,
            'cash_advance_payment_amount' => 0,
            'cash_advance_deduction_schedule' => 'none',
            'cash_advance_start_date' => null,

            'other_loan_total_amount' => 0,
            'other_loan_payment_amount' => 0,
            'other_loan_deduction_schedule' => 'none',
            'other_loan_start_date' => null,

            'is_active' => true,
            'remarks' => null,
        ];
    }

    private function syncOtherDeductions(PayrollEmployeeSalary $salary, array $deductions): void
    {
        $salary->otherDeductions()->delete();

        $rows = collect($deductions)
            ->map(function (array $deduction) {
                $name = $this->cleanText($deduction['name'] ?? null);
                $totalAmount = (float) ($deduction['total_amount'] ?? 0);
                $paymentAmount = (float) ($deduction['payment_amount'] ?? 0);
                $schedule = $deduction['deduction_schedule'] ?? 'none';

                if ($name === null && ($totalAmount > 0 || $paymentAmount > 0)) {
                    $name = 'Other Deduction';
                }

                return [
                    'name' => $name,
                    'total_amount' => round($totalAmount, 2),
                    'payment_amount' => round($paymentAmount, 2),
                    'deduction_schedule' => $schedule ?: 'none',
                    'start_date' => $deduction['start_date'] ?? null,
                    'remarks' => $this->cleanText($deduction['remarks'] ?? null),
                    'is_active' => true,
                ];
            })
            ->filter(function (array $deduction) {
                return ! empty($deduction['name'])
                    && (
                        $deduction['total_amount'] > 0
                        || $deduction['payment_amount'] > 0
                        || $deduction['deduction_schedule'] !== 'none'
                        || ! empty($deduction['start_date'])
                        || ! empty($deduction['remarks'])
                    );
            })
            ->values();

        if ($rows->isEmpty()) {
            return;
        }

        $salary->otherDeductions()->createMany($rows->all());
    }

    private function otherDeductionsPaymentTotal(array $deductions): float
    {
        return collect($deductions)->sum(function (array $deduction) {
            return (float) ($deduction['payment_amount'] ?? 0);
        });
    }

    private function cleanText(mixed $value): ?string
    {
        $cleaned = trim((string) $value);

        return $cleaned === '' ? null : $cleaned;
    }
}
