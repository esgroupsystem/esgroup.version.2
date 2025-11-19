<?php

namespace App\Http\Controllers\HR_Department;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HRDashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::now()->startOfDay();

        // --- Filters (from the blade)
        $deptFilter = $request->get('filter_department');
        $posFilter = $request->get('filter_position');
        $statusFilter = $request->get('filter_status');
        $companyFilter = $request->get('filter_company');

        // Base employee query (apply filters)
        $employeeQuery = Employee::query();

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

        // Totals + basics
        $totalEmployees = Employee::count();
        $filteredTotal = $employeeQuery->count();
        $activeEmployees = Employee::where('status', 'Active')->count();
        $activePct = $totalEmployees > 0 ? round(($activeEmployees / $totalEmployees) * 100, 1) : 0;

        // On leave today: this assumes you store leaves in DriverLeave with start_date/end_date and employee_id
        $onLeaveEmployees = DriverLeave::where(function ($q) use ($today) {
            $q->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today);
        })
            ->whereNotIn('status', ['cancelled'])
            ->distinct('employee_id')
            ->count('employee_id');

        $terminatedEmployees = Employee::where('status', 'Terminated')->count();

        // Offence counts (assumes offense_level on DriverLeave or another offences table)
        $firstOffenses = DriverLeave::where('offense_level', 1)->count();
        $secondOffenses = DriverLeave::where('offense_level', 2)->count();
        $terminationCount = DriverLeave::where('offense_level', '>=', 3)->count();

        // For action / issues: combine offences + terminations + pending clearances (adjust to your logic)
        $forActionCount = $firstOffenses + $secondOffenses + $terminationCount;

        // Employees by department
        $employeesByDept = Employee::selectRaw('department_id, count(*) as total')
            ->with('department:id,name')
            ->groupBy('department_id')
            ->get()
            ->map(function ($r) {
                return [
                    'department' => $r->department?->name ?? 'Unassigned',
                    'total' => (int) $r->total,
                ];
            });

        $deptLabels = $employeesByDept->pluck('department')->toArray();
        $deptData = $employeesByDept->pluck('total')->toArray();

        // Status distribution (Active, On Leave, Terminated, Inactive, Pending Documents)
        // Pending documents: we compute employees missing at least one required doc
        $statusCounts = [
            'Active' => Employee::where('status', 'Active')->count(),
            'On Leave' => $onLeaveEmployees,
            'Terminated' => $terminatedEmployees,
            'Inactive' => Employee::where('status', 'Inactive')->count(),
        ];

        // Document compliance assumptions: Employee model has boolean columns or you have documents table relation.
        // We'll attempt to detect columns; if not present, default to 0.
        $documentCompliance = [
            'birth_certificate' => 0,
            'resume' => 0,
            'contract' => 0,
            'government_ids' => 0,
            'profile_picture' => 0,
        ];

        // Example column names: birth_certificate, resume, contract, government_ids, profile_picture
        // If you use a documents relation, replace with appropriate queries.
        try {
            $documentCompliance['birth_certificate'] = Employee::whereNull('birth_certificate')->orWhere('birth_certificate', '')->count();
            $documentCompliance['resume'] = Employee::whereNull('resume')->orWhere('resume', '')->count();
            $documentCompliance['contract'] = Employee::whereNull('contract')->orWhere('contract', '')->count();
            $documentCompliance['government_ids'] = Employee::whereNull('government_ids')->orWhere('government_ids', '')->count();
            $documentCompliance['profile_picture'] = Employee::whereNull('profile_picture')->orWhere('profile_picture', '')->count();
        } catch (\Exception $e) {
            // If your schema differs, leave zeros — update queries to match your DB
        }

        // Low compliance: employees missing >= 2 documents
        $lowCompliance = collect();
        try {
            $allEmployees = Employee::select('id', 'full_name', 'birth_certificate', 'resume', 'contract', 'government_ids', 'profile_picture')->get();
            foreach ($allEmployees as $emp) {
                $missing = [];
                if (empty($emp->birth_certificate)) {
                    $missing[] = 'Birth Certificate';
                }
                if (empty($emp->resume)) {
                    $missing[] = 'Resume';
                }
                if (empty($emp->contract)) {
                    $missing[] = 'Contract';
                }
                if (empty($emp->government_ids)) {
                    $missing[] = 'Government IDs';
                }
                if (empty($emp->profile_picture)) {
                    $missing[] = 'Profile Picture';
                }
                if (count($missing) >= 2) {
                    $emp->missing_docs = $missing;
                    $lowCompliance->push($emp);
                }
            }
        } catch (\Exception $e) {
            $lowCompliance = collect();
        }

        // Leaves: summary & upcoming
        $leaves = DriverLeave::query();

        // Leave summary counts
        $leaveSummary = [
            'active' => DriverLeave::where('status', 'approved')->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count(),
            'not_started' => DriverLeave::whereDate('start_date', '>', $today)->count(),
            'ongoing' => DriverLeave::whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count(),
            'expired_today' => DriverLeave::whereDate('end_date', $today)->count(),
            'cancelled' => DriverLeave::where('status', 'cancelled')->count(),
            'completed' => DriverLeave::where('status', 'completed')->count(),
        ];

        // upcoming leaves next 7 days
        $upcomingLeaves = DriverLeave::with('employee')
            ->whereDate('start_date', '>=', $today)
            ->whereDate('start_date', '<=', $today->copy()->addDays(7))
            ->orderBy('start_date', 'asc')
            ->get();

        // Recent actions timeline — example: collect recent events from leaves + employees changes
        // You may have an activity log table — replace with your logs if available.
        $timeline = [];
        // Example: last 8 leaves and some employee updates
        $recentLeaves = DriverLeave::with('employee')->orderBy('updated_at', 'desc')->limit(6)->get();
        foreach ($recentLeaves as $rl) {
            $timeline[] = [
                'time' => $rl->updated_at->diffForHumans(),
                'actor' => $rl->employee?->full_name ?? '—',
                'action' => 'Updated Leave ('.$rl->leave_type.')',
            ];
        }

        // Example: recent employee updates
        $recentEmployees = Employee::orderBy('updated_at', 'desc')->limit(4)->get();
        foreach ($recentEmployees as $re) {
            $timeline[] = [
                'time' => $re->updated_at->diffForHumans(),
                'actor' => $re->full_name,
                'action' => 'Updated Profile',
            ];
        }

        // Offences: using DriverLeave->offense_level as example. Replace with your offences table if exists.
        $offences = DriverLeave::with('employee')
            ->whereNotNull('offense_level')
            ->orderBy('updated_at', 'desc')
            ->limit(8)
            ->get()
            ->map(function ($o) {
                $o->level_label = $o->offense_level == 1 ? '1st' : ($o->offense_level == 2 ? '2nd' : '3rd+');
                $o->latest_action = $o->latest_action ?? '—';
                $o->status_label = $o->status ?? 'Active';

                return $o;
            });

        // Attendance summary placeholders (if you have an attendance table, replace accordingly)
        $attendance = [
            'present' => 0,
            'absent' => 0,
            'on_leave' => $onLeaveEmployees,
            'ob' => 0,
        ];

        // Security logs placeholder (replace with actual logs)
        $securityLogs = collect([
            ['time' => now()->subMinutes(10)->diffForHumans(), 'event' => 'Successful login (john@example.com)'],
            ['time' => now()->subHours(2)->diffForHumans(), 'event' => 'Failed login attempt (unknown)'],
        ]);

        // Tasks, announcements, reminders placeholders (replace with DB-driven data)
        $tasks = [
            ['title' => '2 contracts expiring', 'due' => now()->addDays(3)->format('d M Y'), 'note' => 'Notify managers'],
            ['title' => '1 probation ending', 'due' => now()->addDays(7)->format('d M Y'), 'note' => 'Review performance'],
        ];

        $announcements = [
            ['title' => 'Server maintenance', 'published_at' => now()->subDay()->format('d M Y'), 'summary' => 'Scheduled 2AM - 3AM'],
            ['title' => 'New module added', 'published_at' => now()->subDays(3)->format('d M Y'), 'summary' => 'Leave improvements'],
        ];

        $topPerformers = []; // optional: fill from performance data later
        $reminders = [
            '4 leaves ending today', '3 employees require renewal', '1 terminated employee pending clearance',
        ];

        $hrNote = ''; // load saved note from DB if you have one
        $tasksCountExpiring = 2; // example
        $tasksCountProbation = 1;

        // Additional lists: departments, positions for filters
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('title')->get();

        // Build $statusCounts array keys for the chart (ensures consistent ordering)
        $statusCounts = [
            'Active' => $statusCounts['Active'] ?? $statusCounts['Active'] ?? Employee::where('status', 'Active')->count(),
            'On Leave' => $statusCounts['On Leave'] ?? $onLeaveEmployees,
            'Terminated' => $statusCounts['Terminated'] ?? $terminatedEmployees,
            'Inactive' => $statusCounts['Inactive'] ?? Employee::where('status', 'Inactive')->count(),
            'Pending Documents' => $documentCompliance['birth_certificate'] > 0 || $documentCompliance['resume'] > 0 ? ($documentCompliance['birth_certificate'] + $documentCompliance['resume']) : 0,
        ];

        // Prepare data for blade
        return view('hr_department.dashboard_hr', compact(
            'today',
            'totalEmployees',
            'filteredTotal',
            'activeEmployees',
            'activePct',
            'onLeaveEmployees',
            'terminatedEmployees',
            'firstOffenses',
            'secondOffenses',
            'terminationCount',
            'deptLabels',
            'deptData',
            'statusCounts',
            'leaveSummary',
            'upcomingLeaves',
            'timeline',
            'documentCompliance',
            'offences',
            'lowCompliance',
            'attendance',
            'securityLogs',
            'tasks',
            'announcements',
            'topPerformers',
            'reminders',
            'hrNote',
            'tasksCountExpiring',
            'tasksCountProbation',
            'departments',
            'positions',
            'forActionCount',
            'statusCounts'
        ));
    }

    // Optional JSON endpoints for charts if you prefer AJAX
    public function employeesByDeptChart()
    {
        $rows = Employee::selectRaw('department_id, count(*) as total')
            ->with('department:id,name')
            ->groupBy('department_id')
            ->get()
            ->map(fn ($r) => ['label' => $r->department?->name ?? 'Unassigned', 'value' => $r->total]);

        return response()->json($rows);
    }

    public function leavesByTypeChart()
    {
        $rows = DriverLeave::selectRaw('leave_type, count(*) as total')
            ->groupBy('leave_type')
            ->get()
            ->map(fn ($r) => ['label' => $r->leave_type, 'value' => $r->total]);

        return response()->json($rows);
    }
}
