<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\MirasolBiometricsLog;
use App\Models\PayrollAttendanceAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PayrollAttendanceAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->search);

        $adjustments = PayrollAttendanceAdjustment::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%")
                        ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                        ->orWhere('adjustment_type', 'like', "%{$search}%")
                        ->orWhere('adjusted_day_type', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('payroll.attendance_adjustments.index', compact('adjustments', 'search'));
    }

    public function create()
    {
        $people = $this->getBiometricsPeople();

        return view('payroll.attendance_adjustments.create', compact('people'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);

        $duplicate = $this->findDuplicateAdjustment(
            $validated['biometric_employee_id'] ?? null,
            $validated['employee_no'] ?? null,
            $validated['employee_name'],
            $validated['work_date']
        );

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'work_date' => 'Adjustment already exists for this employee on this date.',
            ]);
        }

        PayrollAttendanceAdjustment::create([
            'biometric_employee_id' => $validated['biometric_employee_id'] ?? null,
            'employee_no' => $validated['employee_no'] ?? null,
            'employee_name' => $validated['employee_name'],
            'work_date' => $validated['work_date'],
            'adjustment_type' => $validated['adjustment_type'],
            'adjusted_time_in' => $validated['adjusted_time_in'] ?? null,
            'adjusted_time_out' => $validated['adjusted_time_out'] ?? null,
            'adjusted_day_type' => $validated['adjusted_day_type'] ?? null,
            'is_paid' => $request->boolean('is_paid'),
            'ignore_late' => $request->boolean('ignore_late'),
            'ignore_undertime' => $request->boolean('ignore_undertime'),
            'reason' => $validated['reason'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'encoded_by' => auth()->id(),
            'encoded_at' => now(),
        ]);

        return redirect()
            ->route('payroll-attendance-adjustments.index')
            ->with('success', 'Payroll attendance adjustment saved successfully.');
    }

    public function edit(PayrollAttendanceAdjustment $payrollAttendanceAdjustment)
    {
        $people = $this->getBiometricsPeople();

        return view('payroll.attendance_adjustments.edit', compact('payrollAttendanceAdjustment', 'people'));
    }

    public function update(Request $request, PayrollAttendanceAdjustment $payrollAttendanceAdjustment)
    {
        $validated = $this->validateRequest($request);

        $duplicate = $this->findDuplicateAdjustment(
            $validated['biometric_employee_id'] ?? null,
            $validated['employee_no'] ?? null,
            $validated['employee_name'],
            $validated['work_date'],
            $payrollAttendanceAdjustment->id
        );

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'work_date' => 'Another adjustment already exists for this employee on this date.',
            ]);
        }

        $payrollAttendanceAdjustment->update([
            'biometric_employee_id' => $validated['biometric_employee_id'] ?? null,
            'employee_no' => $validated['employee_no'] ?? null,
            'employee_name' => $validated['employee_name'],
            'work_date' => $validated['work_date'],
            'adjustment_type' => $validated['adjustment_type'],
            'adjusted_time_in' => $validated['adjusted_time_in'] ?? null,
            'adjusted_time_out' => $validated['adjusted_time_out'] ?? null,
            'adjusted_day_type' => $validated['adjusted_day_type'] ?? null,
            'is_paid' => $request->boolean('is_paid'),
            'ignore_late' => $request->boolean('ignore_late'),
            'ignore_undertime' => $request->boolean('ignore_undertime'),
            'reason' => $validated['reason'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return redirect()
            ->route('payroll-attendance-adjustments.index')
            ->with('success', 'Payroll attendance adjustment updated successfully.');
    }

    public function destroy(PayrollAttendanceAdjustment $payrollAttendanceAdjustment)
    {
        $payrollAttendanceAdjustment->delete();

        return redirect()
            ->route('payroll-attendance-adjustments.index')
            ->with('success', 'Payroll attendance adjustment deleted successfully.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'biometric_employee_id' => ['nullable', 'string', 'max:100'],
            'employee_no' => ['nullable', 'string', 'max:100'],
            'employee_name' => ['required', 'string', 'max:255'],
            'work_date' => ['required', 'date'],
            'adjustment_type' => [
                'required',
                Rule::in([
                    'change_schedule',
                    'change_time',
                    'offset',
                    'rest_day_work',
                    'holiday_work',
                    'official_business',
                    'training',
                    'manual_time_in_out',
                    'manual_present',
                    'manual_absent',
                ]),
            ],
            'adjusted_time_in' => ['nullable', 'date_format:H:i'],
            'adjusted_time_out' => ['nullable', 'date_format:H:i', 'after:adjusted_time_in'],
            'adjusted_day_type' => ['nullable', 'string', 'max:50'],
            'reason' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    private function getBiometricsPeople()
    {
        return MirasolBiometricsLog::query()
            ->select([
                'employee_id',
                'employee_no',
                DB::raw('MAX(employee_name) as employee_name'),
            ])
            ->whereNotNull('employee_name')
            ->where('employee_name', '!=', '')
            ->groupBy('employee_id', 'employee_no')
            ->orderBy('employee_name')
            ->get();
    }

    private function findDuplicateAdjustment(
        ?string $biometricEmployeeId,
        ?string $employeeNo,
        string $employeeName,
        string $workDate,
        ?int $ignoreId = null
    ): bool {
        $query = PayrollAttendanceAdjustment::query()
            ->whereDate('work_date', $workDate);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $query->where(function ($q) use ($biometricEmployeeId, $employeeNo, $employeeName) {
            if (! empty($biometricEmployeeId)) {
                $q->orWhere('employee_id', $biometricEmployeeId);
            }

            if (! empty($employeeNo)) {
                $q->orWhere('employee_no', $employeeNo);
            }

            $q->orWhere('employee_name', $employeeName);
        });

        return $query->exists();
    }
}
