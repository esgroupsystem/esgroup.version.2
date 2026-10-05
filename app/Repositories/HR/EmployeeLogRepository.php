<?php

declare(strict_types=1);

namespace App\Repositories\HR;

use App\Models\Employee;
use App\Models\EmployeeLog;
use App\Repositories\Contracts\HR\EmployeeLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EmployeeLogRepository implements EmployeeLogRepositoryInterface
{
    public function add(Employee $employee, ?int $userId, string $action, ?array $meta): EmployeeLog
    {
        return EmployeeLog::query()->create([
            'employee_id' => $employee->id,
            'action' => $action,
            'meta' => $meta,
            'user_id' => $userId,
        ]);
    }

    public function paginateFor(Employee $employee, int $perPage): LengthAwarePaginator
    {
        return $employee->logs()->with('user')->orderByDesc('created_at')->paginate($perPage)->withQueryString();
    }
}
