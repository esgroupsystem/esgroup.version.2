<?php

declare(strict_types=1);

namespace App\Repositories\General;

use App\Enums\LeaveKind;
use App\Models\Department;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Position;
use App\Repositories\Contracts\General\HrReportRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class HrReportRepository implements HrReportRepositoryInterface
{
    private const ACTIVE_STATUS = 'active';

    public function employeeCount(): int
    {
        return Employee::query()->count();
    }

    public function activeEmployeeCount(): int
    {
        return Employee::query()->whereRaw('LOWER(status) = ?', [self::ACTIVE_STATUS])->count();
    }

    public function employeeStatusSummary(): Collection
    {
        return Employee::query()
            ->selectRaw('COALESCE(NULLIF(TRIM(status), ""), "Unknown") as label')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('label')
            ->orderByDesc('total')
            ->get()
            ->toBase();
    }

    public function departmentSummary(): Collection
    {
        return Department::query()
            ->leftJoin('employees', 'employees.department_id', '=', 'departments.id')
            ->select('departments.id', 'departments.name')
            ->selectRaw('COUNT(employees.id) as total_employees')
            ->selectRaw("SUM(CASE WHEN LOWER(COALESCE(employees.status, '')) = ? THEN 1 ELSE 0 END) as active_employees", [self::ACTIVE_STATUS])
            ->selectRaw("SUM(CASE WHEN employees.id IS NOT NULL AND LOWER(COALESCE(employees.status, '')) <> ? THEN 1 ELSE 0 END) as other_status_employees", [self::ACTIVE_STATUS])
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->get()
            ->toBase();
    }

    public function employeesWithMostHistory(int $limit): Collection
    {
        return Employee::query()
            ->with(['department:id,name', 'position:id,title', 'latestHistory.offense'])
            ->withCount('histories')
            ->whereHas('histories')
            ->orderByDesc('histories_count')
            ->limit($limit)
            ->get();
    }

    public function leaveReport(LeaveKind $kind, int $year): array
    {
        $base = fn (): Builder => $kind->modelClass()::query()->whereYear('start_date', $year);
        $breakdown = fn (string $column): Collection => $base()
            ->selectRaw("COALESCE(NULLIF(TRIM({$column}), \"\"), \"Unknown\") as label")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(days), 0) as total_days')
            ->groupBy('label')
            ->orderByDesc('total')
            ->get()
            ->toBase();

        return [
            'total' => $base()->count(),
            'total_days' => (float) $base()->sum('days'),
            'status_breakdown' => $breakdown('status'),
            'type_breakdown' => $breakdown('leave_type'),
            'recent' => $base()
                ->with([
                    'employee:id,employee_id,full_name,department_id,position_id,status',
                    'employee.department:id,name',
                    'employee.position:id,title',
                ])
                ->latest('start_date')
                ->limit(10)
                ->get(),
        ];
    }

    public function holidaysObservedIn(int $year): Collection
    {
        return Holiday::query()->active()->whereYear('observed_date', $year)->orderBy('observed_date')->get();
    }

    public function paginateEmployees(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $search = $filters['q'];

        return Employee::query()
            ->with(['department', 'position'])
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('employee_id', 'like', "%{$search}%")))
            ->when($filters['department'] !== '', fn (Builder $query) => $query->where('department_id', $filters['department']))
            ->when($filters['position'] !== '', fn (Builder $query) => $query->where('position_id', $filters['position']))
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when($filters['company'] !== '', fn (Builder $query) => $query->where('company', $filters['company']))
            ->orderBy('full_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function employeesOnDriverLeave(CarbonInterface $day): int
    {
        return DriverLeave::query()
            ->whereDate('start_date', '<=', $day)
            ->whereDate('end_date', '>=', $day)
            ->whereNotIn('status', ['cancelled'])
            ->distinct('employee_id')
            ->count('employee_id');
    }

    public function driverOffenseLevels(): array
    {
        return [
            'first' => DriverLeave::query()->where('offense_level', 1)->count(),
            'second' => DriverLeave::query()->where('offense_level', 2)->count(),
            'termination' => DriverLeave::query()->where('offense_level', '>=', 3)->count(),
        ];
    }

    public function driverLeaveSummary(CarbonInterface $day): array
    {
        $covering = fn (): Builder => DriverLeave::query()->whereDate('start_date', '<=', $day)->whereDate('end_date', '>=', $day);

        return [
            'active' => $covering()->where('status', 'approved')->count(),
            'not_started' => DriverLeave::query()->whereDate('start_date', '>', $day)->count(),
            'ongoing' => $covering()->count(),
            'expired_today' => DriverLeave::query()->whereDate('end_date', $day)->count(),
            'cancelled' => DriverLeave::query()->where('status', 'cancelled')->count(),
            'completed' => DriverLeave::query()->where('status', 'completed')->count(),
        ];
    }

    public function recentDriverLeaves(int $limit, bool $offensesOnly = false): Collection
    {
        return DriverLeave::query()
            ->with('employee')
            ->when($offensesOnly, fn (Builder $query) => $query->whereNotNull('offense_level'))
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function recentlyUpdatedEmployees(int $limit): Collection
    {
        return Employee::query()->orderByDesc('updated_at')->orderByDesc('created_at')->limit($limit)->get();
    }

    public function employeesByDepartment(): array
    {
        return Department::query()
            ->withCount('employees')
            ->orderByDesc('employees_count')
            ->get()
            ->map(fn (Department $department): array => ['label' => (string) $department->name, 'value' => (int) $department->employees_count])
            ->values()
            ->all();
    }

    public function driverLeavesByType(): array
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

    public function employeeFilterOptions(): array
    {
        return [
            'departments' => Department::query()->orderBy('name')->get(['id', 'name'])
                ->map(fn (Department $department): array => ['value' => (string) $department->id, 'label' => $department->name])->values(),
            'positions' => Position::query()->orderBy('title')->get(['id', 'title'])
                ->map(fn (Position $position): array => ['value' => (string) $position->id, 'label' => $position->title])->values(),
            'statuses' => Employee::query()->whereNotNull('status')->where('status', '!=', '')->distinct()->orderBy('status')->pluck('status'),
        ];
    }
}
