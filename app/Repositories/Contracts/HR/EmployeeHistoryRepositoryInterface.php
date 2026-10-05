<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\HR;

use App\Models\Employee;
use App\Models\EmployeeHistory;

/** Violation history (IR cases): one row per offense, grouped by IR number. */
interface EmployeeHistoryRepositoryInterface
{
    public function findOrFail(Employee $employee, int $historyId): EmployeeHistory;

    /** @param array<string, mixed> $attributes */
    public function create(Employee $employee, array $attributes): EmployeeHistory;

    /** Number of violation rows under one IR number. */
    public function countIrCase(Employee $employee, ?string $irNumber): int;

    /** Deletes every violation row under one IR number. */
    public function deleteIrCase(Employee $employee, ?string $irNumber): void;

    public function delete(EmployeeHistory $history): void;
}
