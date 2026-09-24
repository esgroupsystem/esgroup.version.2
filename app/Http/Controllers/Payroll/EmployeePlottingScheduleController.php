<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Enums\WorkdayType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\SaveEmployeePlottingScheduleRequest;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use App\Services\Payroll\EmployeePlottingScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class EmployeePlottingScheduleController extends Controller
{
    public function __construct(
        private readonly EmployeeBiometricIdentityService $identityService,
        private readonly EmployeePlottingScheduleService $scheduleService
    ) {}

    /**
     * Display one permanent schedule row per active biometric employee.
     */
    public function index(Request $request): Response
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
            ->payrollDirectoryOrder()
            ->paginate(25)
            ->withQueryString();

        $employees->setCollection(
            $employees->getCollection()
                ->map(function (EmployeeBiometric $employee): EmployeeBiometric {
                    $snapshot = $this->identityService->snapshot($employee);

                    $employee->setAttribute('plotting_employee_biometric_id', $employee->id);
                    $employee->setAttribute('plotting_employee_name', $employee->payroll_display_name);
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

                        return ($schedule->status ?? 'scheduled') === $status;
                    })
                    ->values()
            );
        }

        if ($shift !== '') {
            $employees->setCollection(
                $employees->getCollection()
                    ->filter(function (EmployeeBiometric $employee) use ($schedules, $shift): bool {
                        $schedule = $schedules->get((int) $employee->id);

                        return ($schedule->shift_name ?? 'Regular Shift') === $shift;
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
            'eight_hours' => $schedules->filter(
                fn (EmployeePlottingSchedule $schedule): bool => $schedule->resolvedWorkdayType() === WorkdayType::EightHours
            )->count(),
            'nine_hours' => $schedules->filter(
                fn (EmployeePlottingSchedule $schedule): bool => $schedule->resolvedWorkdayType() === WorkdayType::NineHours
            )->count(),
            'straight_eight' => $schedules->filter(
                fn (EmployeePlottingSchedule $schedule): bool => $schedule->resolvedWorkdayType() === WorkdayType::StraightEightHours
            )->count(),
        ];

        $workdayRules = collect(WorkdayType::cases())
            ->mapWithKeys(fn (WorkdayType $type): array => [
                $type->value => [
                    'label' => $type->label(),
                    'short_label' => $type->shortLabel(),
                    'paid_hours' => $type->paidHours(),
                    'paid_minutes' => $type->paidMinutes(),
                    'lunch_minutes' => $type->lunchMinutes(),
                    'clock_minutes' => $type->clockMinutes(),
                ],
            ])
            ->all();
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $employees->through(function (EmployeeBiometric $employee) use ($schedules): array {
            $schedule = $schedules->get((int) $employee->id);

            return [
                'employee_biometric_id' => (int) $employee->id,
                'name' => $employee->plotting_employee_name,
                'employee_no' => $employee->plotting_employee_no,
                'group_name' => $employee->group_name !== null ? (string) $employee->group_name : null,
                'schedule' => [
                    'status' => $schedule->status ?? 'scheduled',
                    'shift_name' => $schedule->shift_name ?? 'Regular Shift',
                    'workday_type' => $schedule?->resolvedWorkdayType()->value ?? WorkdayType::EightHours->value,
                    'time_in' => $schedule?->time_in ? substr((string) $schedule->time_in, 0, 5) : null,
                    'time_out' => $schedule?->time_out ? substr((string) $schedule->time_out, 0, 5) : null,
                    'grace_minutes' => $schedule->grace_minutes ?? 15,
                    'day_offs' => $schedule?->resolvedDayOffs() ?? [],
                    'remarks' => $schedule->remarks ?? '',
                    'is_saved' => $schedule !== null,
                ],
            ];
        });

        return Inertia::render('payroll/plotting/index', [
            'employees' => $employees,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'shift' => $shift,
                'group_name' => $groupName,
            ],
            'groups' => $groups->map(fn ($group): string => (string) $group)->values(),
            'stats' => $stats,
            'workdayRules' => $workdayRules,
            'weekdays' => $weekdays,
            'urls' => [
                'index' => route('payroll-plotting.index'),
                'save' => route('payroll-plotting.save'),
            ],
        ]);
    }

    /**
     * Save one permanent schedule per active biometric employee.
     */
    public function save(SaveEmployeePlottingScheduleRequest $request): RedirectResponse
    {
        $this->scheduleService->savePermanentSchedules(
            $request->validated('schedule', [])
        );

        return redirect()
            ->route('payroll-plotting.index', $request->only(['search', 'status', 'shift', 'group_name']))
            ->with('success', 'Permanent schedule saved successfully. Rebuild Attendance Summary before payroll checking.');
    }
}
