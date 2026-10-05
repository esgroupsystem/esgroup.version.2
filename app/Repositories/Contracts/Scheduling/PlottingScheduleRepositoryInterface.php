<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Scheduling;

use App\Models\EmployeePlottingSchedule;
use Illuminate\Support\Collection;

/** Permanent work schedules (Work Schedule page): one row per person, work_date null. */
interface PlottingScheduleRepositoryInterface
{
    public function permanentFor(int $employeeBiometricId): ?EmployeePlottingSchedule;

    /** The schedule that applies on a date: a dated row for that day, else the permanent one (latest edit wins). */
    public function scheduleOn(int $employeeBiometricId, string $date): ?EmployeePlottingSchedule;

    /**
     * Replaces the person's permanent schedule with a new row.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function replacePermanent(int $employeeBiometricId, array $attributes): EmployeePlottingSchedule;

    /**
     * People with a schedule (one per employee number), optionally matching a search on name,
     * employee no, biometric id or CrossChex id.
     *
     * @return Collection<int, array{employee_no: ?string, employee_name: ?string, biometric_employee_id: ?string}>
     */
    public function people(?string $search = null): Collection;

    /**
     * Every schedule row (dated and permanent) of these employee numbers or biometric ids, latest edit first.
     *
     * @param  Collection<int, string>  $employeeNos
     * @param  Collection<int, string>  $biometricIds
     * @return Collection<int, EmployeePlottingSchedule>
     */
    public function forPeople(Collection $employeeNos, Collection $biometricIds): Collection;
}
