<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Models\Employee;
use App\Repositories\Contracts\HR\EmployeeLogRepositoryInterface;

/** Writes the employee profile's audit trail, as the signed-in user. */
final class EmployeeAuditService
{
    public function __construct(
        private readonly EmployeeLogRepositoryInterface $logs,
    ) {}

    /** @param array<string, mixed> $meta */
    public function log(Employee $employee, string $action, array $meta = []): void
    {
        $userId = auth()->id();

        $this->logs->add($employee, $userId === null ? null : (int) $userId, $action, $meta ?: null);
    }
}
