<?php

namespace App\Services\Reports;

use App\Models\ConductorLeave;
use App\Models\Department;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\Holiday;
use Illuminate\Support\Collection;

class HrDataReportService
{
    private const ACTIVE_STATUS = 'active';

    public function getDashboardData(int $year): array
    {
        $employeeStatusSummary = $this->getEmployeeStatusSummary();

        $totalEmployees = Employee::query()->count();

        $activeEmployees = Employee::query()
            ->whereRaw('LOWER(status) = ?', [self::ACTIVE_STATUS])
            ->count();

        $otherStatusEmployees = $totalEmployees - $activeEmployees;

        return [
            'year' => $year,

            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'otherStatusEmployees' => $otherStatusEmployees,

            'employeeStatusSummary' => $employeeStatusSummary,
            'activeVsOtherSummary' => collect([
                [
                    'label' => 'Active',
                    'total' => $activeEmployees,
                ],
                [
                    'label' => 'Other Status',
                    'total' => $otherStatusEmployees,
                ],
            ]),

            'departmentSummary' => $this->getDepartmentSummary(),

            'employeeHistoryReport' => $this->getEmployeeHistoryReport(),

            'leaveReports' => collect([
                $this->getLeaveReport(EmployeeLeave::class, 'Admin / Office Leave', $year),
                $this->getLeaveReport(DriverLeave::class, 'Driver Leave', $year),
                $this->getLeaveReport(ConductorLeave::class, 'Conductor Leave', $year),
            ]),

            'holidays' => $this->getHolidayCalendar($year),
        ];
    }

    private function getEmployeeStatusSummary(): Collection
    {
        return Employee::query()
            ->selectRaw('COALESCE(NULLIF(TRIM(status), ""), "Unknown") as label')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();
    }

    private function getDepartmentSummary(): Collection
    {
        return Department::query()
            ->leftJoin('employees', 'employees.department_id', '=', 'departments.id')
            ->select('departments.id', 'departments.name')
            ->selectRaw('COUNT(employees.id) as total_employees')
            ->selectRaw("
                SUM(
                    CASE
                        WHEN LOWER(COALESCE(employees.status, '')) = ?
                        THEN 1
                        ELSE 0
                    END
                ) as active_employees
            ", [self::ACTIVE_STATUS])
            ->selectRaw("
                SUM(
                    CASE
                        WHEN employees.id IS NOT NULL
                        AND LOWER(COALESCE(employees.status, '')) <> ?
                        THEN 1
                        ELSE 0
                    END
                ) as other_status_employees
            ", [self::ACTIVE_STATUS])
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->get();
    }

    private function getEmployeeHistoryReport(): Collection
    {
        return Employee::query()
            ->with([
                'department:id,name',
                'position:id,title',
                'latestHistory.offense',
            ])
            ->withCount('histories')
            ->whereHas('histories')
            ->orderByDesc('histories_count')
            ->limit(25)
            ->get();
    }

    private function getLeaveReport(string $modelClass, string $label, int $year): array
    {
        $baseQuery = fn () => $modelClass::query()
            ->whereYear('start_date', $year);

        $statusBreakdown = $baseQuery()
            ->selectRaw('COALESCE(NULLIF(TRIM(status), ""), "Unknown") as label')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(days), 0) as total_days')
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        $typeBreakdown = $baseQuery()
            ->selectRaw('COALESCE(NULLIF(TRIM(leave_type), ""), "Unknown") as label')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(days), 0) as total_days')
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        $recentLeaves = $baseQuery()
            ->with([
                'employee:id,employee_id,full_name,department_id,position_id,status',
                'employee.department:id,name',
                'employee.position:id,title',
            ])
            ->latest('start_date')
            ->limit(10)
            ->get();

        return [
            'label' => $label,
            'total' => $baseQuery()->count(),
            'total_days' => $baseQuery()->sum('days'),
            'status_breakdown' => $statusBreakdown,
            'type_breakdown' => $typeBreakdown,
            'recent' => $recentLeaves,
        ];
    }

    private function getHolidayCalendar(int $year): Collection
    {
        return Holiday::query()
            ->active()
            ->whereYear('observed_date', $year)
            ->orderBy('observed_date')
            ->get()
            ->groupBy(fn ($holiday) => $holiday->observed_date->format('F'));
    }
}
