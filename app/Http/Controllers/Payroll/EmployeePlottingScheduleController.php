<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\EmployeePlottingSchedule;
use App\Models\MirasolBiometricsLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class EmployeePlottingScheduleController extends Controller
{
    /**
     * Display one permanent schedule row per employee.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $shift = trim((string) $request->query('shift', ''));

        $employees = MirasolBiometricsLog::query()
            ->selectRaw("\n                MIN(employee_id) AS biometric_employee_id,\n                TRIM(employee_no) AS employee_no,\n                MIN(NULLIF(TRIM(employee_name), '')) AS employee_name\n            ")
            ->whereNotNull('employee_no')
            ->whereRaw("TRIM(employee_no) <> ''")
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('employee_no', 'like', "%{$search}%")
                        ->orWhere('employee_name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->groupBy(DB::raw('TRIM(employee_no)'))
            ->orderByRaw("MIN(NULLIF(TRIM(employee_name), '')) ASC")
            ->paginate(25)
            ->withQueryString();

        $employeeNos = $employees->getCollection()
            ->pluck('employee_no')
            ->map(fn ($employeeNo) => trim((string) $employeeNo))
            ->filter()
            ->values();

        $scheduleQuery = EmployeePlottingSchedule::query()
            ->whereIn('employee_no', $employeeNos);

        if (Schema::hasColumn((new EmployeePlottingSchedule)->getTable(), 'work_date')) {
            $scheduleQuery->whereNull('work_date');
        }

        $schedules = $scheduleQuery
            ->get()
            ->keyBy(fn ($schedule) => trim((string) $schedule->employee_no));

        if ($status !== '') {
            $employees->setCollection(
                $employees->getCollection()->filter(function ($employee) use ($schedules, $status) {
                    $employeeNo = trim((string) $employee->employee_no);
                    $schedule = $schedules->get($employeeNo);

                    return ($schedule?->status ?? 'scheduled') === $status;
                })->values()
            );
        }

        if ($shift !== '') {
            $employees->setCollection(
                $employees->getCollection()->filter(function ($employee) use ($schedules, $shift) {
                    $employeeNo = trim((string) $employee->employee_no);
                    $schedule = $schedules->get($employeeNo);

                    return ($schedule?->shift_name ?? 'Regular Shift') === $shift;
                })->values()
            );
        }

        $stats = [
            'visible_employees' => $employees->count(),
            'saved_permanent' => $schedules->count(),
            'scheduled' => $schedules->where('status', 'scheduled')->count(),
            'rest_day' => $schedules->where('status', 'rest_day')->count(),
            'inactive' => $schedules->where('status', 'inactive')->count(),
            'regular' => $schedules->where('shift_name', 'Regular Shift')->count(),
            'flexible' => $schedules->where('shift_name', 'Flexible Shift')->count(),
        ];

        return view('payroll.plotting.index', compact(
            'employees',
            'schedules',
            'search',
            'status',
            'shift',
            'stats'
        ));
    }

    /**
     * Save one permanent schedule per employee.
     */
    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'schedule' => ['nullable', 'array'],
            'schedule.*.biometric_employee_id' => ['nullable'],
            'schedule.*.employee_no' => ['required', 'string', 'max:100'],
            'schedule.*.employee_name' => ['nullable', 'string', 'max:255'],
            'schedule.*.status' => ['required', 'string', 'in:scheduled,rest_day,inactive'],
            'schedule.*.shift_name' => ['required', 'string', 'in:Regular Shift,Flexible Shift'],
            'schedule.*.time_in' => ['nullable', 'date_format:H:i'],
            'schedule.*.time_out' => ['nullable', 'date_format:H:i'],
            'schedule.*.grace_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'schedule.*.day_off' => ['nullable', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'schedule.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $rows = $validated['schedule'] ?? [];

        foreach ($rows as $index => $row) {
            $status = $row['status'] ?? 'scheduled';
            $shiftName = $row['shift_name'] ?? 'Regular Shift';
            $timeIn = $row['time_in'] ?? null;
            $timeOut = $row['time_out'] ?? null;

            if ($status === 'scheduled' && $shiftName === 'Regular Shift' && (blank($timeIn) || blank($timeOut))) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "schedule.{$index}.time_in" => 'Regular Shift must have both Time In and Time Out before saving permanent schedule.',
                    ]);
            }

            if ($status === 'scheduled' && $shiftName === 'Regular Shift' && $timeIn === $timeOut) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "schedule.{$index}.time_out" => 'Time In and Time Out cannot be the same for Regular Shift.',
                    ]);
            }
        }

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $employeeNo = trim((string) ($row['employee_no'] ?? ''));

                if ($employeeNo === '') {
                    continue;
                }

                $status = $row['status'] ?? 'scheduled';
                $shiftName = $row['shift_name'] ?? 'Regular Shift';
                $isFlexible = $shiftName === 'Flexible Shift';
                $isNonWorkingStatus = in_array($status, ['rest_day', 'inactive'], true);

                $payload = [
                    'crosschex_id' => null,
                    'biometric_employee_id' => trim((string) ($row['biometric_employee_id'] ?? '')) ?: null,
                    'employee_no' => $employeeNo,
                    'employee_name' => $row['employee_name'] ?? '',
                    'work_date' => null,
                    'shift_name' => $shiftName,
                    'time_in' => ($isFlexible || $isNonWorkingStatus) ? null : ($row['time_in'] ?? null),
                    'time_out' => ($isFlexible || $isNonWorkingStatus) ? null : ($row['time_out'] ?? null),
                    'grace_minutes' => (int) ($row['grace_minutes'] ?? 15),
                    'status' => $status,
                    'day_off' => $row['day_off'] ?? null,
                    'remarks' => $row['remarks'] ?? null,
                ];

                /*
                 * Important:
                 * Delete old cutoff/date plotting rows for this employee first.
                 * This guarantees only one permanent schedule controls payroll summary.
                 */
                EmployeePlottingSchedule::query()
                    ->where('employee_no', $employeeNo)
                    ->delete();

                EmployeePlottingSchedule::query()->create($payload);
            }
        });

        return redirect()
            ->route('payroll-plotting.index', $request->only(['search', 'status', 'shift']))
            ->with('success', 'Permanent schedule saved successfully. Please rebuild Attendance Summary before payroll checking.');
    }
}
