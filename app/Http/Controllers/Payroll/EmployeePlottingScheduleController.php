<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class EmployeePlottingScheduleController extends Controller
{
    public function __construct(
        private readonly EmployeeBiometricIdentityService $identityService
    ) {}

    /**
     * Display one permanent schedule row per active biometric employee.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));
        $shift = trim((string) $request->query('shift', ''));
        $groupName = trim((string) $request->query('group_name', ''));

        $employees = EmployeeBiometric::query()
            ->with('company')
            ->payrollActive()
            ->group($groupName)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('display_employee_no', 'like', "%{$search}%")
                        ->orWhere('display_name', 'like', "%{$search}%")
                        ->orWhere('source_employee_no', 'like', "%{$search}%")
                        ->orWhere('source_employee_id', 'like', "%{$search}%")
                        ->orWhere('source_employee_name', 'like', "%{$search}%")
                        ->orWhere('source_crosschex_id', 'like', "%{$search}%")
                        ->orWhere('source_crosschex_account_name', 'like', "%{$search}%")
                        ->orWhere('source_crosschex_account', 'like', "%{$search}%")
                        ->orWhere('group_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('group_name')
            ->orderByRaw("
            COALESCE(
                NULLIF(display_name, ''),
                NULLIF(source_employee_name, ''),
                NULLIF(source_crosschex_account_name, ''),
                NULLIF(source_crosschex_account, ''),
                'Unknown Employee'
            ) ASC
        ")
            ->paginate(25)
            ->withQueryString();

        $employees->setCollection(
            $employees->getCollection()
                ->map(function (EmployeeBiometric $employee): EmployeeBiometric {
                    $snapshot = $this->identityService->snapshot($employee);

                    $employee->setAttribute('plotting_employee_biometric_id', $employee->id);
                    $employee->setAttribute('plotting_employee_name', $snapshot['employee_name'] ?? 'Unknown Employee');
                    $employee->setAttribute('plotting_employee_no', $snapshot['employee_no'] ?? null);
                    $employee->setAttribute('plotting_biometric_employee_id', $snapshot['biometric_employee_id'] ?? null);
                    $employee->setAttribute('plotting_crosschex_id', $snapshot['crosschex_id'] ?? null);

                    return $employee;
                })
        );

        $employeeIds = $employees->getCollection()
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values();

        $scheduleQuery = EmployeePlottingSchedule::query()
            ->whereIn('employee_biometric_id', $employeeIds);

        if (Schema::hasColumn((new EmployeePlottingSchedule)->getTable(), 'work_date')) {
            $scheduleQuery->whereNull('work_date');
        }

        $schedules = $scheduleQuery
            ->get()
            ->keyBy(fn (EmployeePlottingSchedule $schedule): int => (int) $schedule->employee_biometric_id);

        if ($status !== '') {
            $employees->setCollection(
                $employees->getCollection()
                    ->filter(function (EmployeeBiometric $employee) use ($schedules, $status): bool {
                        $schedule = $schedules->get((int) $employee->id);

                        return ($schedule?->status ?? 'scheduled') === $status;
                    })
                    ->values()
            );
        }

        if ($shift !== '') {
            $employees->setCollection(
                $employees->getCollection()
                    ->filter(function (EmployeeBiometric $employee) use ($schedules, $shift): bool {
                        $schedule = $schedules->get((int) $employee->id);

                        return ($schedule?->shift_name ?? 'Regular Shift') === $shift;
                    })
                    ->values()
            );
        }

        $groups = EmployeeBiometric::query()
            ->payrollActive()
            ->whereNotNull('group_name')
            ->where('group_name', '!=', '')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name');

        $stats = [
            'visible_employees' => $employees->count(),
            'saved_permanent' => $schedules->count(),
            'scheduled' => $schedules->where('status', 'scheduled')->count(),
            'rest_day' => $schedules->where('status', 'rest_day')->count(),
            'inactive' => EmployeeBiometric::query()->inactive()->count(),
            'regular' => $schedules->where('shift_name', 'Regular Shift')->count(),
            'flexible' => $schedules->where('shift_name', 'Flexible Shift')->count(),
        ];

        return view('payroll.plotting.index', compact(
            'employees',
            'schedules',
            'search',
            'status',
            'shift',
            'groupName',
            'groups',
            'stats'
        ));
    }

    /**
     * Save one permanent schedule per active biometric employee.
     */
    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'schedule' => ['nullable', 'array'],
            'schedule.*.employee_biometric_id' => ['required', 'integer', 'exists:employee_biometrics,id'],
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

        DB::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                $employee = EmployeeBiometric::query()
                    ->whereKey((int) $row['employee_biometric_id'])
                    ->first();

                if (! $employee) {
                    continue;
                }

                $snapshot = $this->identityService->snapshot($employee);

                $status = $row['status'] ?? 'scheduled';
                $shiftName = $row['shift_name'] ?? 'Regular Shift';
                $isFlexible = $shiftName === 'Flexible Shift';
                $isNonWorkingStatus = in_array($status, ['rest_day', 'inactive'], true);

                if ($status === 'inactive') {
                    $employee->markPayrollInactive($row['remarks'] ?? 'Marked inactive from schedule plotting.');
                }

                $payload = [
                    'employee_biometric_id' => $employee->id,
                    'crosschex_id' => $snapshot['crosschex_id'],
                    'biometric_employee_id' => $snapshot['biometric_employee_id'],
                    'employee_no' => $snapshot['employee_no'],
                    'employee_name' => $snapshot['employee_name'],
                    'work_date' => null,
                    'shift_name' => $shiftName,
                    'time_in' => ($isFlexible || $isNonWorkingStatus) ? null : ($row['time_in'] ?? null),
                    'time_out' => ($isFlexible || $isNonWorkingStatus) ? null : ($row['time_out'] ?? null),
                    'grace_minutes' => (int) ($row['grace_minutes'] ?? 15),
                    'status' => $status,
                    'day_off' => $row['day_off'] ?? null,
                    'remarks' => $row['remarks'] ?? null,
                ];

                EmployeePlottingSchedule::query()
                    ->where('employee_biometric_id', $employee->id)
                    ->whereNull('work_date')
                    ->delete();

                EmployeePlottingSchedule::query()->create($payload);
            }
        });

        return redirect()
            ->route('payroll-plotting.index', $request->only(['search', 'status', 'shift', 'group_name']))
            ->with('success', 'Permanent schedule saved successfully. Please rebuild Attendance Summary before payroll checking.');
    }
}
