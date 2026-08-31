<?php

declare(strict_types=1);

namespace App\Services\HR_Department;

use App\Models\Employee;
use App\Models\EmployeeLog;

final class EmployeeAuditService
{
    /** @param array<string, mixed> $meta */
    public function log(Employee $employee, string $action, array $meta = []): void
    {
        EmployeeLog::query()->create([
            'employee_id' => $employee->id,
            'action' => $action,
            'meta' => $meta ?: null,
            'user_id' => auth()->id(),
        ]);
    }
}
