<?php

declare(strict_types=1);

namespace App\Repositories\HR;

use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Repositories\Contracts\HR\EmployeeHistoryRepositoryInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class EmployeeHistoryRepository implements EmployeeHistoryRepositoryInterface
{
    public function findOrFail(Employee $employee, int $historyId): EmployeeHistory
    {
        return $employee->histories()->findOrFail($historyId);
    }

    public function create(Employee $employee, array $attributes): EmployeeHistory
    {
        return $employee->histories()->create($attributes);
    }

    public function countIrCase(Employee $employee, ?string $irNumber): int
    {
        return $this->irCase($employee, $irNumber)->count();
    }

    public function deleteIrCase(Employee $employee, ?string $irNumber): void
    {
        $this->irCase($employee, $irNumber)->delete();
    }

    public function delete(EmployeeHistory $history): void
    {
        $history->delete();
    }

    /** @return HasMany<EmployeeHistory, Employee> */
    private function irCase(Employee $employee, ?string $irNumber): HasMany
    {
        return $employee->histories()->where('title', 'Violations')->where('ir_number', $irNumber);
    }
}
