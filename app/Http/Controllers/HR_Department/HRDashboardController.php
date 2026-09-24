<?php

declare(strict_types=1);

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Models\Position;
use App\Support\HR\EmployeePagePresenter;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HRDashboardController extends Controller
{
    /**
     * Show HR dashboard.
     * - keeps only the data needed for the simplified HR dashboard.
     */
    public function index(Request $request): Response
    {
        $today = Carbon::now()->startOfDay();

        $search = trim((string) $request->input('q', ''));
        $deptFilter = $request->input('filter_department');
        $posFilter = $request->input('filter_position');
        $statusFilter = $request->input('filter_status');
        $companyFilter = $request->input('filter_company');

        // Employees (with filters)
        $employeeQuery = Employee::with(['department', 'position'])
            ->orderBy('full_name');

        if ($search !== '') {
            $employeeQuery->where(function ($query) use ($search): void {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }
        if ($deptFilter) {
            $employeeQuery->where('department_id', $deptFilter);
        }
        if ($posFilter) {
            $employeeQuery->where('position_id', $posFilter);
        }
        if ($statusFilter) {
            $employeeQuery->where('status', $statusFilter);
        }
        if ($companyFilter) {
            $employeeQuery->where('company', $companyFilter);
        }

        $employees = $employeeQuery->paginate(20)->withQueryString();

        // KPI
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'Active')->count();
        $activePct = $totalEmployees > 0 ? round(($activeEmployees / $totalEmployees) * 100, 1) : 0;

        $onLeaveEmployees = DriverLeave::whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereNotIn('status', ['cancelled'])
            ->distinct('employee_id')
            ->count('employee_id');

        // NOTE: Your offences counts are currently based on DriverLeave.
        // If you have a separate Offence model/table later, replace this logic.
        $firstOffenses = DriverLeave::where('offense_level', 1)->count();
        $secondOffenses = DriverLeave::where('offense_level', 2)->count();
        $terminationCount = DriverLeave::where('offense_level', '>=', 3)->count();
        $forActionCount = $firstOffenses + $secondOffenses + $terminationCount;

        // Leave Summary
        $leaveSummary = [
            'active' => DriverLeave::where('status', 'approved')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->count(),

            'not_started' => DriverLeave::whereDate('start_date', '>', $today)->count(),

            'ongoing' => DriverLeave::whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->count(),

            'expired_today' => DriverLeave::whereDate('end_date', $today)->count(),

            'cancelled' => DriverLeave::where('status', 'cancelled')->count(),

            'completed' => DriverLeave::where('status', 'completed')->count(),
        ];

        // Timeline (SAFE: handles null updated_at)
        $timeline = [];

        $recentLeaves = DriverLeave::with('employee')
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        foreach ($recentLeaves as $rl) {
            $date = $rl->updated_at ?? $rl->created_at;

            $timeline[] = [
                'time' => $date ? $date->diffForHumans() : '—',
                'actor' => $rl->employee->full_name ?? '—',
                'action' => 'Updated Leave ('.($rl->leave_type ?? 'Leave').')',
                'kind' => 'leave',
            ];
        }

        $recentEmployees = Employee::orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        foreach ($recentEmployees as $re) {
            $date = $re->updated_at ?? $re->created_at;

            $timeline[] = [
                'time' => $date ? $date->diffForHumans() : '—',
                'actor' => $re->full_name ?? '—',
                'action' => 'Updated Profile',
                'kind' => 'profile',
            ];
        }

        // Offences (still using DriverLeave as placeholder)
        $offences = DriverLeave::with('employee')
            ->whereNotNull('offense_level')
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn (DriverLeave $o): array => [
                'id' => $o->id,
                'employee' => $o->employee->full_name ?? '—',
                'level' => $o->offense_level === 1 ? '1st' : ($o->offense_level === 2 ? '2nd' : '3rd+'),
                'status' => $o->status ?? 'Active',
                'when' => ($o->updated_at ?? $o->created_at)?->diffForHumans() ?? '—',
            ]);

        return Inertia::render('dashboards/hr/index', [
            'today' => $today->format('l, F j, Y'),
            'employees' => $employees->through(fn (Employee $employee): array => [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'email' => $employee->email,
                'department' => $employee->department?->name,
                'position' => $employee->position?->title,
                'status' => $employee->status,
                'show_url' => route('employees.staff.show', $employee->id),
            ]),
            'kpis' => [
                'total' => $totalEmployees,
                'active' => $activeEmployees,
                'active_pct' => $activePct,
                'on_leave' => $onLeaveEmployees,
                'for_action' => $forActionCount,
                'offense_levels' => ['first' => $firstOffenses, 'second' => $secondOffenses, 'termination' => $terminationCount],
            ],
            'leaveSummary' => $leaveSummary,
            'timeline' => $timeline,
            'offences' => $offences,
            'employeesByDepartment' => $this->employeesByDepartment(),
            'leavesByType' => $this->leavesByType(),
            'filters' => [
                'q' => $search,
                'department' => (string) ($deptFilter ?? ''),
                'position' => (string) ($posFilter ?? ''),
                'status' => (string) ($statusFilter ?? ''),
                'company' => (string) ($companyFilter ?? ''),
            ],
            'options' => [
                'departments' => Department::orderBy('name')->get(['id', 'name'])->map(fn (Department $department): array => ['value' => (string) $department->id, 'label' => $department->name]),
                'positions' => Position::orderBy('title')->get(['id', 'title'])->map(fn (Position $position): array => ['value' => (string) $position->id, 'label' => $position->title]),
                'statuses' => Employee::query()->whereNotNull('status')->where('status', '!=', '')->distinct()->orderBy('status')->pluck('status'),
                'companies' => EmployeePagePresenter::COMPANIES,
            ],
            'links' => [
                'employees' => route('employees.staff.index'),
                'leaves' => route('driver-leave.driver.index'),
                'offenses' => route('violation.offenses.index'),
                'departments' => route('employees.departments.index'),
                'users' => route('authentication.users.index'),
            ],
            'urls' => ['index' => route('hr.dashboard')],
        ]);
    }

    /** JSON: employee headcount per department (was routed but never implemented). */
    public function employeesByDeptChart(): JsonResponse
    {
        return response()->json($this->employeesByDepartment());
    }

    /** JSON: driver leave count per leave type (was routed but never implemented). */
    public function leavesByTypeChart(): JsonResponse
    {
        return response()->json($this->leavesByType());
    }

    /** @return list<array{label: string, value: int}> */
    private function employeesByDepartment(): array
    {
        return Department::query()
            ->withCount('employees')
            ->orderByDesc('employees_count')
            ->get()
            ->map(fn (Department $department): array => ['label' => (string) $department->name, 'value' => (int) $department->employees_count])
            ->values()
            ->all();
    }

    /** @return list<array{label: string, value: int}> */
    private function leavesByType(): array
    {
        return DriverLeave::query()
            ->selectRaw('COALESCE(NULLIF(TRIM(leave_type), ""), "Unknown") as label, COUNT(*) as total')
            ->groupBy('label')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row): array => ['label' => (string) $row->label, 'value' => (int) $row->total])
            ->values()
            ->all();
    }
}
