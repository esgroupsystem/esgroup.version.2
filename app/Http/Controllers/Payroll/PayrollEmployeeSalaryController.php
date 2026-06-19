<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\MirasolBiometricsLog;
use App\Models\PayrollEmployeeSalary;
use App\Services\Payroll\PayrollDeductionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayrollEmployeeSalaryController extends Controller
{
    public function __construct(
        private readonly PayrollDeductionService $deductionService
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->search);

        $canonicalSalaryIds = PayrollEmployeeSalary::query()
            ->selectRaw('COALESCE(MAX(CASE WHEN basic_salary > 0 THEN id END), MIN(id))')
            ->groupByRaw(
                "COALESCE(NULLIF(employee_no, ''), NULLIF(crosschex_id, ''), NULLIF(biometric_employee_id, ''), CONCAT('name:', LOWER(TRIM(employee_name))))"
            );

        $salaries = PayrollEmployeeSalary::query()
            ->with('otherDeductions')
            ->whereIn('id', $canonicalSalaryIds)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%")
                        ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                        ->orWhere('crosschex_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('employee_name')
            ->orderBy('employee_no')
            ->paginate(15)
            ->withQueryString();

        $salaries->getCollection()->transform(function (PayrollEmployeeSalary $salary) {
            $salary->payroll_preview = $this->deductionService->salaryPreview($salary);

            return $salary;
        });

        return view('payroll.employee_salaries.index', compact('salaries', 'search'));
    }

    public function create(): View
    {
        $people = $this->biometricPeople();

        return view('payroll.employee_salaries.create', compact('people'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $computed = $this->deductionService->computeRates(
            (float) $validated['basic_salary'],
            $validated['rate_type']
        );

        DB::transaction(function () use ($validated, $computed) {
            $salary = PayrollEmployeeSalary::create($this->payload($validated, $computed, true));

            $this->syncOtherDeductions($salary, $validated['other_deductions'] ?? []);
        });

        return redirect()
            ->route('payroll-employee-salaries.index')
            ->with('success', 'Salary record created successfully.');
    }

    public function edit(PayrollEmployeeSalary $payrollEmployeeSalary): View
    {
        $payrollEmployeeSalary->load('otherDeductions');

        return view('payroll.employee_salaries.edit', [
            'salary' => $payrollEmployeeSalary,
        ]);
    }

    public function update(Request $request, PayrollEmployeeSalary $payrollEmployeeSalary): RedirectResponse
    {
        $validated = $request->validate($this->rules($payrollEmployeeSalary));

        $computed = $this->deductionService->computeRates(
            (float) $validated['basic_salary'],
            $validated['rate_type']
        );

        DB::transaction(function () use ($payrollEmployeeSalary, $validated, $computed) {
            $payrollEmployeeSalary->update($this->payload($validated, $computed, false));

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
        $people = $this->biometricPeople();
        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($people as $person) {
            $employeeNo = $this->cleanText($person->employee_no ?? null);
            $employeeName = $this->cleanText($person->employee_name ?? null);
            $crosschexId = $this->cleanText($person->crosschex_id ?? null);
            $biometricEmployeeId = $this->cleanText($person->biometric_employee_id ?? null)
                ?? $crosschexId
                ?? $employeeNo;

            if (empty($employeeName) || empty($biometricEmployeeId)) {
                $skipped++;

                continue;
            }

            $existingSalary = $this->findExistingSalaryProfile(
                $employeeNo,
                $crosschexId,
                $biometricEmployeeId,
                $employeeName
            );

            if ($existingSalary) {
                $existingSalary->update([
                    'employee_no' => $existingSalary->employee_no ?: $employeeNo,
                    'employee_name' => $employeeName,
                    'crosschex_id' => $existingSalary->crosschex_id ?: $crosschexId,
                    'biometric_employee_id' => $existingSalary->biometric_employee_id ?: $biometricEmployeeId,
                ]);

                $updated++;

                continue;
            }

            PayrollEmployeeSalary::create($this->defaultSalaryPayload(
                $employeeNo,
                $employeeName,
                $crosschexId,
                $biometricEmployeeId
            ));

            $inserted++;
        }

        return redirect()
            ->route('payroll-employee-salaries.index')
            ->with(
                'success',
                "Biometrics sync completed. {$inserted} added, {$updated} updated, {$skipped} skipped. Duplicate employees will no longer be created."
            );
    }

    private function biometricPeople()
    {
        return MirasolBiometricsLog::query()
            ->select([
                'employee_no',
                'employee_name',
                'crosschex_id',
            ])
            ->whereNotNull('employee_name')
            ->where('employee_name', '!=', '')
            ->orderBy('employee_name')
            ->orderByDesc('id')
            ->get()
            ->map(function ($person) {
                $person->employee_no = $this->cleanText($person->employee_no ?? null);
                $person->employee_name = $this->cleanText($person->employee_name ?? null);
                $person->crosschex_id = $this->cleanText($person->crosschex_id ?? null);
                $person->biometric_employee_id = $person->crosschex_id ?: $person->employee_no;

                return $person;
            })
            ->filter(function ($person) {
                return ! empty($person->employee_name)
                    && (! empty($person->employee_no) || ! empty($person->crosschex_id));
            })
            ->unique(function ($person) {
                if (! empty($person->employee_no)) {
                    return 'empno:'.strtolower($person->employee_no);
                }

                if (! empty($person->crosschex_id)) {
                    return 'crosschex:'.strtolower($person->crosschex_id);
                }

                return 'name:'.strtolower($person->employee_name);
            })
            ->values();
    }

    private function findExistingSalaryProfile(
        ?string $employeeNo,
        ?string $crosschexId,
        ?string $biometricEmployeeId,
        ?string $employeeName
    ): ?PayrollEmployeeSalary {
        return PayrollEmployeeSalary::query()
            ->where(function ($query) use ($employeeNo, $crosschexId, $biometricEmployeeId, $employeeName) {
                $hasIdentifier = false;

                if (! empty($employeeNo)) {
                    $hasIdentifier = true;
                    $query->orWhere('employee_no', $employeeNo);
                }

                if (! empty($crosschexId)) {
                    $hasIdentifier = true;
                    $query->orWhere('crosschex_id', $crosschexId)
                        ->orWhere('biometric_employee_id', $crosschexId);
                }

                if (! empty($biometricEmployeeId)) {
                    $hasIdentifier = true;
                    $query->orWhere('biometric_employee_id', $biometricEmployeeId);
                }

                if (! $hasIdentifier && ! empty($employeeName)) {
                    $query->orWhereRaw('LOWER(TRIM(employee_name)) = ?', [strtolower($employeeName)]);
                }
            })
            ->orderByDesc('is_active')
            ->orderByDesc('basic_salary')
            ->orderBy('id')
            ->first();
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

        $rules = [
            'employee_no' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('payroll_employee_salaries', 'employee_no')->ignore($ignoreId),
            ],
            'employee_name' => ['required', 'string', 'max:255'],
            'crosschex_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('payroll_employee_salaries', 'crosschex_id')->ignore($ignoreId),
            ],

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

        if ($salary === null) {
            $rules['biometric_employee_id'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('payroll_employee_salaries', 'biometric_employee_id'),
            ];
        }

        return $rules;
    }

    private function payload(array $validated, array $computed, bool $isCreate): array
    {
        $employeeNo = $this->cleanText($validated['employee_no'] ?? null);
        $crosschexId = $this->cleanText($validated['crosschex_id'] ?? null);

        $payload = [
            'employee_no' => $employeeNo,
            'employee_name' => $this->cleanText($validated['employee_name']) ?? $validated['employee_name'],
            'crosschex_id' => $crosschexId,

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

            'is_active' => (bool) ($validated['is_active'] ?? false),
            'remarks' => $validated['remarks'] ?? null,
        ];

        if ($isCreate) {
            $payload['biometric_employee_id'] = $this->cleanText($validated['biometric_employee_id'])
                ?? $crosschexId
                ?? $employeeNo;
        }

        return $payload;
    }

    private function defaultSalaryPayload(
        ?string $employeeNo,
        string $employeeName,
        ?string $crosschexId,
        string $biometricEmployeeId
    ): array {
        return [
            'biometric_employee_id' => $biometricEmployeeId,
            'employee_no' => $employeeNo,
            'employee_name' => $employeeName,
            'crosschex_id' => $crosschexId,

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
