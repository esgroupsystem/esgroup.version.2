<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\HR;

use App\Models\Employee;
use App\Models\EmployeeLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** The audit trail on the employee profile. */
interface EmployeeLogRepositoryInterface
{
    /** @param array<string, mixed>|null $meta */
    public function add(Employee $employee, ?int $userId, string $action, ?array $meta): EmployeeLog;

    /** @return LengthAwarePaginator<int, EmployeeLog> newest first, with the user */
    public function paginateFor(Employee $employee, int $perPage): LengthAwarePaginator;
}
