<?php

declare(strict_types=1);

namespace App\Http\Controllers\Chairman;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Holiday;
use App\Services\Reports\HrDataReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class HrDataController extends Controller
{
    public function __construct(
        private readonly HrDataReportService $hrDataReportService
    ) {}

    public function index(Request $request): Response
    {
        $year = (int) $request->integer('year', now()->year);
        $data = $this->hrDataReportService->getDashboardData($year);

        $breakdown = fn (Collection $rows): array => $rows
            ->map(fn ($row): array => ['label' => (string) $row->label, 'value' => (int) $row->total])
            ->values()
            ->all();

        return Inertia::render('dashboards/hr-data/index', [
            'year' => $year,
            'years' => range(now()->year + 1, now()->year - 5),
            'totals' => [
                'employees' => $data['totalEmployees'],
                'active' => $data['activeEmployees'],
                'other' => $data['otherStatusEmployees'],
                'departments' => $data['departmentSummary']->count(),
            ],
            'statusSummary' => $breakdown($data['employeeStatusSummary']),
            'departments' => $data['departmentSummary']->map(fn ($department): array => [
                'name' => (string) $department->name,
                'total' => (int) $department->total_employees,
                'active' => (int) $department->active_employees,
                'other' => (int) $department->other_status_employees,
            ])->values(),
            'leaveReports' => $data['leaveReports']->map(fn (array $report): array => [
                'label' => $report['label'],
                'total' => (int) $report['total'],
                'total_days' => (float) $report['total_days'],
                'by_status' => $breakdown($report['status_breakdown']),
                'by_type' => array_slice($breakdown($report['type_breakdown']), 0, 5),
                'recent' => $report['recent']->map(fn ($leave): array => [
                    'id' => $leave->id,
                    'employee' => $leave->employee?->full_name ?? 'No employee record',
                    'department' => $leave->employee?->department?->name ?? 'N/A',
                    'leave_type' => $leave->leave_type ?? 'N/A',
                    'start' => $leave->start_date?->format('M d, Y'),
                    'end' => $leave->end_date?->format('M d, Y'),
                    'days' => (float) ($leave->days ?? 0),
                    'status' => $leave->status ?? 'Unknown',
                ])->values(),
            ])->values(),
            'employeeHistory' => $data['employeeHistoryReport']->map(function (Employee $employee): array {
                $latest = $employee->latestHistory;

                return [
                    'id' => $employee->id,
                    'employee_id' => $employee->employee_id ?? ($employee->employee_id_permanent ?? 'N/A'),
                    'name' => $employee->full_name,
                    'department' => $employee->department?->name ?? 'N/A',
                    'position' => $employee->position?->title ?? 'N/A',
                    'count' => (int) $employee->histories_count,
                    'ir_number' => $latest?->ir_number ?? 'N/A',
                    'title' => $latest?->title ?? 'N/A',
                    'offense' => $latest?->offense ? ($latest->offense->name ?? ($latest->offense->title ?? 'Recorded offense')) : null,
                    'remarks' => $latest?->remarks ?? ($latest?->description ?? 'No remarks'),
                ];
            })->values(),
            'holidays' => $data['holidays']->map(fn (Collection $holidays, string $month): array => [
                'month' => $month,
                'items' => $holidays->map(fn (Holiday $holiday): array => [
                    'id' => $holiday->id,
                    'name' => $holiday->name,
                    'date' => $holiday->observed_date->format('M d, Y'),
                    'moved_from' => $holiday->is_moved ? $holiday->actual_date?->format('M d, Y') : null,
                    'type' => (string) $holiday->holiday_type,
                ])->values(),
            ])->values(),
            'urls' => ['index' => route('chairman.hr-data.index')],
        ]);
    }
}
