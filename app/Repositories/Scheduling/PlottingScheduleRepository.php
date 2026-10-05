<?php

declare(strict_types=1);

namespace App\Repositories\Scheduling;

use App\Models\EmployeePlottingSchedule;
use App\Repositories\Contracts\Scheduling\PlottingScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class PlottingScheduleRepository implements PlottingScheduleRepositoryInterface
{
    public function permanentFor(int $employeeBiometricId): ?EmployeePlottingSchedule
    {
        return $this->permanent($employeeBiometricId)->latest('id')->first();
    }

    public function scheduleOn(int $employeeBiometricId, string $date): ?EmployeePlottingSchedule
    {
        return EmployeePlottingSchedule::query()
            ->where('employee_biometric_id', $employeeBiometricId)
            ->whereDate('work_date', $date)
            ->latest('updated_at')
            ->latest('id')
            ->first()
            ?? $this->permanent($employeeBiometricId)->latest('updated_at')->latest('id')->first();
    }

    public function replacePermanent(int $employeeBiometricId, array $attributes): EmployeePlottingSchedule
    {
        $this->permanent($employeeBiometricId)->delete();

        return EmployeePlottingSchedule::query()->create(['employee_biometric_id' => $employeeBiometricId, 'work_date' => null] + $attributes);
    }

    public function people(?string $search = null): Collection
    {
        return EmployeePlottingSchedule::query()
            ->selectRaw('MIN(biometric_employee_id) AS biometric_employee_id')
            ->selectRaw('TRIM(employee_no) AS employee_no')
            ->selectRaw("MIN(NULLIF(TRIM(employee_name), '')) AS employee_name")
            ->when(
                $search === null,
                fn (Builder $query) => $query->whereNotNull('employee_name')->whereRaw("TRIM(employee_name) <> ''"),
                fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                    ->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('employee_no', 'like', "%{$search}%")
                    ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                    ->orWhere('crosschex_id', 'like', "%{$search}%"))
            )
            ->groupBy(DB::raw('TRIM(employee_no)'))
            ->get()
            ->toBase()
            ->map(fn (EmployeePlottingSchedule $row): array => [
                'employee_no' => $row->employee_no,
                'employee_name' => $row->employee_name,
                'biometric_employee_id' => $row->biometric_employee_id,
            ]);
    }

    public function forPeople(Collection $employeeNos, Collection $biometricIds): Collection
    {
        return EmployeePlottingSchedule::query()
            ->where(function (Builder $query) use ($employeeNos, $biometricIds): void {
                if ($employeeNos->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('TRIM(employee_no)'), $employeeNos->all());
                }
                if ($biometricIds->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('TRIM(biometric_employee_id)'), $biometricIds->all());
                }
            })
            ->orderByDesc('updated_at')
            ->get();
    }

    /** @return Builder<EmployeePlottingSchedule> */
    private function permanent(int $employeeBiometricId): Builder
    {
        return EmployeePlottingSchedule::query()->where('employee_biometric_id', $employeeBiometricId)->whereNull('work_date');
    }
}
