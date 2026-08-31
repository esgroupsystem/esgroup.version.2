<?php

declare(strict_types=1);

namespace App\Services\HR_Department;

use App\Enums\EmployeeStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class EmployeeDirectoryService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     employees: LengthAwarePaginator,
     *     departments: Collection,
     *     positions: Collection,
     *     employeeStats: array<string, int>,
     *     companies: Collection,
     *     garages: Collection,
     *     statusOptions: list<string>
     * }
     */
    public function indexData(array $filters): array
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $query = Employee::query()->with(['position', 'department']);
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('employee_id_permanent', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('garage', 'like', "%{$search}%")
                    ->orWhereHas('position', fn ($positionQuery) => $positionQuery->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('name', 'like', "%{$search}%"));
            });
        }

        foreach (['status', 'company', 'garage'] as $filter) {
            if (filled($filters[$filter] ?? null)) {
                $query->where($filter, $filters[$filter]);
            }
        }

        $employees = $query->orderBy('full_name')->paginate($perPage)->appends($filters);
        $departments = Department::query()
            ->with(['positions' => fn ($query) => $query->orderBy('title')])
            ->orderBy('name')
            ->get();
        $positions = Position::query()->orderBy('title')->get();

        return [
            'employees' => $employees,
            'departments' => $departments,
            'positions' => $positions,
            'employeeStats' => [
                'total' => Employee::query()->count(),
                'active' => Employee::query()->whereIn('status', EmployeeStatus::activeValues())->count(),
                'inactive' => Employee::query()->whereIn('status', EmployeeStatus::inactiveValues())->count(),
                'suspended' => Employee::query()->where('status', EmployeeStatus::Suspended->value)->count(),
                'companies' => Employee::query()->whereNotNull('company')->where('company', '!=', '')->distinct()->count('company'),
                'garages' => Employee::query()->whereNotNull('garage')->where('garage', '!=', '')->distinct()->count('garage'),
            ],
            'companies' => Employee::query()->whereNotNull('company')->where('company', '!=', '')->distinct()->orderBy('company')->pluck('company'),
            'garages' => Employee::query()->whereNotNull('garage')->where('garage', '!=', '')->distinct()->orderBy('garage')->pluck('garage'),
            'statusOptions' => EmployeeStatus::values(),
        ];
    }
}
