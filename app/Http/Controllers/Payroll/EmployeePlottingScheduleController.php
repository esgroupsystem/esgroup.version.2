<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\EmployeePlottingSchedule;
use App\Models\MirasolBiometricsLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployeePlottingScheduleController extends Controller
{
    /**
     * Show permanent plotting schedule.
     */
    public function index(Request $request): View
    {
        $employees = MirasolBiometricsLog::query()
            ->selectRaw("
                MIN(employee_id) AS biometric_employee_id,
                TRIM(employee_no) AS employee_no,
                MIN(NULLIF(TRIM(employee_name), '')) AS employee_name
            ")
            ->whereNotNull('employee_no')
            ->whereRaw("TRIM(employee_no) <> ''")
            ->groupBy(DB::raw('TRIM(employee_no)'))
            ->orderBy('employee_name')
            ->paginate(20)
            ->withQueryString();

        $employeeNos = $employees->getCollection()
            ->pluck('employee_no')
            ->map(fn ($employeeNo) => trim((string) $employeeNo))
            ->filter()
            ->values();

        $schedules = EmployeePlottingSchedule::query()
            ->whereIn('employee_no', $employeeNos)
            ->get()
            ->keyBy(fn ($schedule) => trim((string) $schedule->employee_no));

        return view('payroll.plotting.index', compact('employees', 'schedules'));
    }

    /**
     * Save permanent schedule.
     */
    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'schedule' => ['nullable', 'array'],
            'schedule.*.biometric_employee_id' => ['nullable'],
            'schedule.*.employee_no' => ['required'],
            'schedule.*.employee_name' => ['nullable', 'string', 'max:255'],
            'schedule.*.status' => ['nullable', 'string', 'max:50'],
            'schedule.*.shift_name' => ['nullable', 'string', 'max:255'],
            'schedule.*.time_in' => ['nullable', 'date_format:H:i'],
            'schedule.*.time_out' => ['nullable', 'date_format:H:i'],
            'schedule.*.grace_minutes' => ['nullable', 'integer', 'min:0'],
            'schedule.*.day_off' => ['nullable', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'schedule.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $rows = $validated['schedule'] ?? [];

        foreach ($rows as $row) {
            $employeeNo = trim((string) ($row['employee_no'] ?? ''));
            if ($employeeNo === '') {
                continue;
            }

            $biometricEmployeeId = trim((string) ($row['biometric_employee_id'] ?? ''));

            EmployeePlottingSchedule::updateOrCreate(
                ['employee_no' => $employeeNo],
                [
                    'biometric_employee_id' => $biometricEmployeeId,
                    'employee_name' => $row['employee_name'] ?? '',
                    'shift_name' => $row['shift_name'] ?? 'Regular Shift',
                    'time_in' => $row['time_in'] ?? null,
                    'time_out' => $row['time_out'] ?? null,
                    'grace_minutes' => $row['grace_minutes'] ?? 15,
                    'status' => $row['status'] ?? 'scheduled',
                    'day_off' => $row['day_off'] ?? null,
                    'remarks' => $row['remarks'] ?? '',
                ]
            );
        }

        return redirect()
            ->route('payroll-plotting.index')
            ->with('success', 'Permanent schedule saved successfully.');
    }
}
