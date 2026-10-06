<?php

declare(strict_types=1);

namespace App\Http\Controllers\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scheduling\SaveWorkScheduleRequest;
use App\Http\Resources\Scheduling\WorkScheduleRowResource;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Services\Scheduling\WorkScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Scheduling & Rates → Work Schedule: one permanent schedule per payroll-active person,
 * edited as a grid and saved together.
 */
final class WorkScheduleController extends Controller
{
    private const FILTERS = ['search', 'status', 'shift', 'group_name'];

    public function __construct(
        private readonly WorkScheduleService $schedules,
    ) {}

    public function index(Request $request): Response
    {
        $filters = [];
        foreach (self::FILTERS as $key) {
            $filters[$key] = trim((string) $request->query($key, ''));
        }

        $page = $this->schedules->paginate($filters['search'], $filters['group_name'], $filters['status'], $filters['shift']);
        $stats = $this->schedules->stats($page);

        return Inertia::render('payroll/plotting/index', [
            'employees' => $page->through(fn (EmployeeBiometric $employee): array => (new WorkScheduleRowResource($employee, $this->schedules->identity($employee)))->resolve($request)),
            'filters' => $filters,
            'groups' => $this->schedules->groups()->values(),
            'stats' => $stats,
            'workdayRules' => $this->schedules->workdayRules(),
            'weekdays' => EmployeePlottingSchedule::WEEKDAYS,
            'urls' => [
                'index' => route('payroll-plotting.index'),
                'save' => route('payroll-plotting.save'),
            ],
        ]);
    }

    public function save(SaveWorkScheduleRequest $request): RedirectResponse
    {
        $this->schedules->savePermanentSchedules($request->validated('schedule', []));

        if ($profile = $request->integer('return_profile')) {
            return to_route('biometrics.employees.show', $profile)
                ->with('success', 'Work schedule saved. Rebuild Attendance Summary before payroll checking.');
        }

        return redirect()
            ->route('payroll-plotting.index', $request->only(self::FILTERS))
            ->with('success', 'Permanent schedule saved successfully. Rebuild Attendance Summary before payroll checking.');
    }
}
