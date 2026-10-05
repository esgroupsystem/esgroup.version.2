<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeBiometric;
use App\Models\EmployeeHistory;
use App\Models\EmployeeLog;
use App\Models\HrOffense;
use App\Repositories\Contracts\HR\DepartmentRepositoryInterface;
use App\Repositories\Contracts\HR\EmployeeLogRepositoryInterface;
use App\Repositories\Contracts\HR\EmployeeRepositoryInterface;
use App\Repositories\Contracts\HR\HrOffenseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/** Everything the employee 201 profile page reads. */
final class EmployeeProfileService
{
    private const LOGS_PER_PAGE = 3;

    public function __construct(
        private readonly EmployeeRepositoryInterface $employees,
        private readonly DepartmentRepositoryInterface $departments,
        private readonly HrOffenseRepositoryInterface $offenses,
        private readonly EmployeeLogRepositoryInterface $logs,
        private readonly BiometricLinkService $biometricLinks,
    ) {}

    /**
     * @return array{
     *     employee: Employee,
     *     irCases: Collection<int|string, Collection<int, EmployeeHistory>>,
     *     logs: LengthAwarePaginator<int, EmployeeLog>,
     *     departmentNames: Collection<int, string>,
     *     positionTitles: Collection<int, string>,
     *     departments: Collection<int, Department>,
     *     offenses: Collection<int, HrOffense>,
     * }
     */
    public function profile(Employee $employee): array
    {
        $this->employees->load($employee, [
            'asset', 'attachments', 'position', 'department',
            'histories' => fn (HasMany $query) => $query->with('offense')->orderByDesc('created_at'),
        ]);

        return [
            'employee' => $employee,
            'irCases' => $employee->histories
                ->where('title', 'Violations')
                ->groupBy(fn (EmployeeHistory $history): string => $history->ir_number ?: 'NO-IR'),
            'logs' => $this->logs->paginateFor($employee, self::LOGS_PER_PAGE),
            'departmentNames' => $this->departments->names(),
            'positionTitles' => $this->departments->positionTitles(),
            'departments' => $this->departments->optionsWithPositions(),
            'offenses' => $this->offenses->allBySection(),
        ];
    }

    /** The linked biometric record (with company) for the Biometrics card, or null. */
    public function linkedBiometric(Employee $employee): ?EmployeeBiometric
    {
        return $this->employees->load($employee, ['biometric.company'])->biometric;
    }

    /** @return list<array{value: string, label: string, hint: string}> biometric records that can be linked */
    public function biometricOptions(Employee $employee): array
    {
        return $this->biometricLinks->biometricOptions($employee);
    }
}
