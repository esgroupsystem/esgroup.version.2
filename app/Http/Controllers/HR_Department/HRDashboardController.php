<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HRDashboardController extends Controller
{
    /**
     * Show HR dashboard.
     * - keeps only the data needed for the simplified HR dashboard.
     */
    public function index(Request $request)
    {
        $today = Carbon::now()->startOfDay();

        $deptFilter = $request->get('filter_department');
        $posFilter = $request->get('filter_position');
        $statusFilter = $request->get('filter_status');
        $companyFilter = $request->get('filter_company');

        // Employees (with filters)
        $employeeQuery = Employee::with(['department', 'position'])
            ->orderBy('full_name');

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
                'actor' => $rl->employee?->full_name ?? '—',
                'action' => 'Updated Leave (' . ($rl->leave_type ?? 'Leave') . ')',
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
            ];
        }

        // Offences (still using DriverLeave as placeholder)
        $offences = DriverLeave::with('employee')
            ->whereNotNull('offense_level')
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(function ($o) {
                $o->level_label = $o->offense_level == 1 ? '1st' : ($o->offense_level == 2 ? '2nd' : '3rd+');
                $o->status_label = $o->status ?? 'Active';
                return $o;
            });

        // Filters data
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();

        return view('hr_department.dashboard_hr', compact(
            'today',
            'employees',
            'totalEmployees',
            'activeEmployees',
            'activePct',
            'onLeaveEmployees',
            'forActionCount',
            'leaveSummary',
            'timeline',
            'offences',
            'departments',
            'positions'
        ));
    }
}
