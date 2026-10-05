<?php

declare(strict_types=1);

namespace App\Services\General;

use App\Enums\LeaveKind;
use App\Models\Employee;
use App\Models\Holiday;
use App\Repositories\Contracts\General\HrReportRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * The two hidden HR report pages: the HR dashboard (`hr.dashboard`, headcount, driver leaves and
 * the employee list) and the All Data report (`chairman.hr-data.index`, a year of leaves,
 * departments, IR history and holidays).
 */
final class HrReportService
{
    private const LEAVE_REPORTS = [
        'Admin / Office Leave' => LeaveKind::Employee,
        'Driver Leave' => LeaveKind::Driver,
        'Conductor Leave' => LeaveKind::Conductor,
    ];

    public function __construct(private readonly HrReportRepositoryInterface $reports) {}

    /**
     * @return array{totals: array{employees: int, active: int, other: int}, statusSummary: Collection<int, object>, departments: Collection<int, object>, employeeHistory: Collection<int, Employee>, leaveReports: list<array<string, mixed>>, holidays: Collection<string, Collection<int, Holiday>>}
     */
    public function allData(int $year): array
    {
        $total = $this->reports->employeeCount();
        $active = $this->reports->activeEmployeeCount();

        $leaveReports = [];
        foreach (self::LEAVE_REPORTS as $label => $kind) {
            $leaveReports[] = ['label' => $label, ...$this->reports->leaveReport($kind, $year)];
        }

        return [
            'totals' => ['employees' => $total, 'active' => $active, 'other' => $total - $active],
            'statusSummary' => $this->reports->employeeStatusSummary(),
            'departments' => $this->reports->departmentSummary(),
            'employeeHistory' => $this->reports->employeesWithMostHistory(25),
            'leaveReports' => $leaveReports,
            'holidays' => $this->reports->holidaysObservedIn($year)->groupBy(fn (Holiday $holiday): string => $holiday->observed_date->format('F')),
        ];
    }

    /**
     * @param  array{q: string, department: string, position: string, status: string, company: string}  $filters
     * @return array{employees: LengthAwarePaginator<int, Employee>, kpis: array<string, mixed>, leaveSummary: array<string, int>, timeline: list<array<string, string>>, offences: Collection<int, array<string, mixed>>, options: array<string, mixed>}
     */
    public function hrDashboard(array $filters, CarbonInterface $today): array
    {
        $total = $this->reports->employeeCount();
        $active = $this->reports->activeEmployeeCount();
        $levels = $this->reports->driverOffenseLevels();

        return [
            'employees' => $this->reports->paginateEmployees($filters),
            'kpis' => [
                'total' => $total,
                'active' => $active,
                'active_pct' => $total > 0 ? round(($active / $total) * 100, 1) : 0,
                'on_leave' => $this->reports->employeesOnDriverLeave($today),
                // Offense levels are still read from driver leaves (`offense_level`), as before.
                'for_action' => array_sum($levels),
                'offense_levels' => $levels,
            ],
            'leaveSummary' => $this->reports->driverLeaveSummary($today),
            'timeline' => $this->timeline(),
            'offences' => $this->reports->recentDriverLeaves(8, offensesOnly: true)->map(fn ($leave): array => [
                'id' => $leave->id,
                'employee' => $leave->employee->full_name ?? '—',
                'level' => $leave->offense_level === 1 ? '1st' : ($leave->offense_level === 2 ? '2nd' : '3rd+'),
                'status' => $leave->status ?? 'Active',
                'when' => ($leave->updated_at ?? $leave->created_at)?->diffForHumans() ?? '—',
            ]),
            'options' => [...$this->reports->employeeFilterOptions(), 'companies' => Employee::COMPANIES],
        ];
    }

    /** @return list<array{label: string, value: int}> */
    public function employeesByDepartment(): array
    {
        return $this->reports->employeesByDepartment();
    }

    /** @return list<array{label: string, value: int}> */
    public function driverLeavesByType(): array
    {
        return $this->reports->driverLeavesByType();
    }

    /** @return list<array{time: string, actor: string, action: string, kind: string}> 6 latest leaves, then 4 latest profiles */
    private function timeline(): array
    {
        $leaves = $this->reports->recentDriverLeaves(6)->map(fn ($leave): array => [
            'time' => ($leave->updated_at ?? $leave->created_at)?->diffForHumans() ?? '—',
            'actor' => $leave->employee->full_name ?? '—',
            'action' => 'Updated Leave ('.($leave->leave_type ?? 'Leave').')',
            'kind' => 'leave',
        ]);
        $profiles = $this->reports->recentlyUpdatedEmployees(4)->map(fn (Employee $employee): array => [
            'time' => ($employee->updated_at ?? $employee->created_at)?->diffForHumans() ?? '—',
            'actor' => $employee->full_name ?? '—',
            'action' => 'Updated Profile',
            'kind' => 'profile',
        ]);

        return [...$leaves->all(), ...$profiles->all()];
    }
}
