<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\MirasolBiometricsLog;
use App\Models\PayrollEmployeeSalary;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PayrollEmployeeSalaryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->search);

        $salaries = PayrollEmployeeSalary::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%")
                        ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                        ->orWhere('crosschex_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('employee_name')
            ->paginate(15)
            ->withQueryString();

        return view('payroll.employee_salaries.index', compact('salaries', 'search'));
    }

    public function create()
    {
        $people = MirasolBiometricsLog::query()
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
            ->filter(function ($person) {
                return ! empty($person->employee_no) || ! empty($person->crosschex_id) || ! empty($person->employee_name);
            })
            ->unique(function ($person) {
                if (! empty($person->employee_no)) {
                    return 'empno:'.strtolower(trim($person->employee_no));
                }

                return 'name:'.strtolower(trim($person->employee_name));
            })
            ->map(function ($person) {
                $person->biometric_employee_id = $person->crosschex_id;

                return $person;
            })
            ->values();

        return view('payroll.employee_salaries.create', compact('people'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'biometric_employee_id' => ['required', 'string', 'max:255', 'unique:payroll_employee_salaries,biometric_employee_id'],
            'employee_no' => ['nullable', 'string', 'max:255'],
            'employee_name' => ['required', 'string', 'max:255'],
            'crosschex_id' => ['nullable', 'string', 'max:255'],
            'rate_type' => ['required', Rule::in(['daily', 'monthly'])],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowance' => ['nullable', 'numeric', 'min:0'],
            'ot_rate_per_hour' => ['nullable', 'numeric', 'min:0'],
            'late_deduction_per_minute' => ['nullable', 'numeric', 'min:0'],
            'undertime_deduction_per_minute' => ['nullable', 'numeric', 'min:0'],
            'absent_deduction_per_day' => ['nullable', 'numeric', 'min:0'],
            'sss_loan' => ['nullable', 'numeric', 'min:0'],
            'pagibig_loan' => ['nullable', 'numeric', 'min:0'],
            'vale' => ['nullable', 'numeric', 'min:0'],
            'other_loans' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string'],
        ]);

        $computed = $this->computeRates(
            (float) $validated['basic_salary'],
            $validated['rate_type']
        );

        PayrollEmployeeSalary::create([
            'biometric_employee_id' => $validated['biometric_employee_id'],
            'employee_no' => $validated['employee_no'] ?? null,
            'employee_name' => $validated['employee_name'],
            'crosschex_id' => $validated['crosschex_id'] ?? null,
            'rate_type' => $validated['rate_type'],
            'basic_salary' => $validated['basic_salary'],
            'allowance' => $validated['allowance'] ?? 0,
            'ot_rate_per_hour' => $validated['ot_rate_per_hour'] ?? $computed['hourly_rate'],
            'late_deduction_per_minute' => $computed['per_minute_rate'],
            'undertime_deduction_per_minute' => $computed['per_minute_rate'],
            'absent_deduction_per_day' => $computed['daily_rate'],
            'sss_loan' => $validated['sss_loan'] ?? 0,
            'pagibig_loan' => $validated['pagibig_loan'] ?? 0,
            'vale' => $validated['vale'] ?? 0,
            'other_loans' => $validated['other_loans'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()
            ->route('payroll-employee-salaries.index')
            ->with('success', 'Salary record created successfully.');
    }

    public function edit(PayrollEmployeeSalary $payrollEmployeeSalary)
    {
        return view('payroll.employee_salaries.edit', [
            'salary' => $payrollEmployeeSalary,
        ]);
    }

    public function update(Request $request, PayrollEmployeeSalary $payrollEmployeeSalary)
    {
        $validated = $request->validate([
            'employee_no' => ['nullable', 'string', 'max:255'],
            'employee_name' => ['required', 'string', 'max:255'],
            'crosschex_id' => ['nullable', 'string', 'max:255'],
            'rate_type' => ['required', Rule::in(['daily', 'monthly'])],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowance' => ['nullable', 'numeric', 'min:0'],
            'ot_rate_per_hour' => ['nullable', 'numeric', 'min:0'],
            'late_deduction_per_minute' => ['nullable', 'numeric', 'min:0'],
            'undertime_deduction_per_minute' => ['nullable', 'numeric', 'min:0'],
            'absent_deduction_per_day' => ['nullable', 'numeric', 'min:0'],
            'sss_loan' => ['nullable', 'numeric', 'min:0'],
            'pagibig_loan' => ['nullable', 'numeric', 'min:0'],
            'vale' => ['nullable', 'numeric', 'min:0'],
            'other_loans' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string'],
        ]);

        $computed = $this->computeRates(
            (float) $validated['basic_salary'],
            $validated['rate_type']
        );

        $payrollEmployeeSalary->update([
            'employee_no' => $validated['employee_no'] ?? null,
            'employee_name' => $validated['employee_name'],
            'crosschex_id' => $validated['crosschex_id'] ?? null,
            'rate_type' => $validated['rate_type'],
            'basic_salary' => $validated['basic_salary'],
            'allowance' => $validated['allowance'] ?? 0,
            'ot_rate_per_hour' => $validated['ot_rate_per_hour'] ?? $computed['hourly_rate'],
            'late_deduction_per_minute' => $computed['per_minute_rate'],
            'undertime_deduction_per_minute' => $computed['per_minute_rate'],
            'absent_deduction_per_day' => $computed['daily_rate'],
            'sss_loan' => $validated['sss_loan'] ?? 0,
            'pagibig_loan' => $validated['pagibig_loan'] ?? 0,
            'vale' => $validated['vale'] ?? 0,
            'other_loans' => $validated['other_loans'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()
            ->route('payroll-employee-salaries.index')
            ->with('success', 'Salary record updated successfully.');
    }

    public function destroy(PayrollEmployeeSalary $payrollEmployeeSalary)
    {
        $payrollEmployeeSalary->delete();

        return redirect()
            ->route('payroll-employee-salaries.index')
            ->with('success', 'Salary record deleted successfully.');
    }

    public function syncFromBiometrics()
    {
        $people = MirasolBiometricsLog::query()
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
            ->filter(function ($person) {
                return ! empty($person->employee_no) || ! empty($person->crosschex_id) || ! empty($person->employee_name);
            })
            ->unique(function ($person) {
                if (! empty($person->employee_no)) {
                    return 'empno:'.strtolower(trim($person->employee_no));
                }

                return 'name:'.strtolower(trim($person->employee_name));
            })
            ->values();

        $inserted = 0;

        foreach ($people as $person) {
            $biometricEmployeeId = $person->crosschex_id;

            if (empty($biometricEmployeeId)) {
                continue;
            }

            $exists = PayrollEmployeeSalary::where('biometric_employee_id', $biometricEmployeeId)->exists();

            if (! $exists) {
                PayrollEmployeeSalary::create([
                    'biometric_employee_id' => $biometricEmployeeId,
                    'employee_no' => $person->employee_no,
                    'employee_name' => $person->employee_name,
                    'crosschex_id' => $person->crosschex_id,
                    'rate_type' => 'daily',
                    'basic_salary' => 0,
                    'allowance' => 0,
                    'ot_rate_per_hour' => 0,
                    'late_deduction_per_minute' => 0,
                    'undertime_deduction_per_minute' => 0,
                    'absent_deduction_per_day' => 0,
                    'sss_loan' => 0,
                    'pagibig_loan' => 0,
                    'vale' => 0,
                    'other_loans' => 0,
                    'is_active' => true,
                    'remarks' => null,
                ]);

                $inserted++;
            }
        }

        return redirect()
            ->route('payroll-employee-salaries.index')
            ->with('success', "Biometrics sync completed. {$inserted} employee salary record(s) added.");
    }

    private function computeRates(float $basicSalary, string $rateType): array
    {
        $workingDaysPerMonth = 22;
        $hoursPerDay = 8;
        $minutesPerHour = 60;

        if ($basicSalary <= 0) {
            return [
                'daily_rate' => 0,
                'hourly_rate' => 0,
                'per_minute_rate' => 0,
            ];
        }

        $dailyRate = $rateType === 'monthly'
            ? ($basicSalary / $workingDaysPerMonth)
            : $basicSalary;

        $hourlyRate = $dailyRate / $hoursPerDay;
        $perMinuteRate = $hourlyRate / $minutesPerHour;

        return [
            'daily_rate' => round($dailyRate, 2),
            'hourly_rate' => round($hourlyRate, 2),
            'per_minute_rate' => round($perMinuteRate, 4),
        ];
    }
}
