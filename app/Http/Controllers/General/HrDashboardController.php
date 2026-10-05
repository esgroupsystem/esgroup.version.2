<?php

declare(strict_types=1);

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\General\HrReportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** The hidden HR dashboard (hr.dashboard*), not in the sidebar. */
final class HrDashboardController extends Controller
{
    public function __construct(private readonly HrReportService $reportService) {}

    public function index(Request $request): Response
    {
        $today = Carbon::now()->startOfDay();
        $filters = [
            'q' => trim((string) $request->input('q', '')),
            'department' => (string) $request->input('filter_department', ''),
            'position' => (string) $request->input('filter_position', ''),
            'status' => (string) $request->input('filter_status', ''),
            'company' => (string) $request->input('filter_company', ''),
        ];
        $data = $this->reportService->hrDashboard($filters, $today);

        return Inertia::render('dashboards/hr/index', [
            'today' => $today->format('l, F j, Y'),
            'employees' => $data['employees']->through(fn (Employee $employee): array => [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'email' => $employee->email,
                'department' => $employee->department?->name,
                'position' => $employee->position?->title,
                'status' => $employee->status,
                'show_url' => route('employees.staff.show', $employee->id),
            ]),
            'kpis' => $data['kpis'],
            'leaveSummary' => $data['leaveSummary'],
            'timeline' => $data['timeline'],
            'offences' => $data['offences'],
            'employeesByDepartment' => $this->reportService->employeesByDepartment(),
            'leavesByType' => $this->reportService->driverLeavesByType(),
            'filters' => $filters,
            'options' => $data['options'],
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

    /** JSON: employee headcount per department. */
    public function employeesByDeptChart(): JsonResponse
    {
        return response()->json($this->reportService->employeesByDepartment());
    }

    /** JSON: driver leave count per leave type. */
    public function leavesByTypeChart(): JsonResponse
    {
        return response()->json($this->reportService->driverLeavesByType());
    }
}
