<?php

declare(strict_types=1);

namespace App\Repositories\Biometrics;

use App\Models\MirasolBiometricsLog;
use App\Repositories\Contracts\Biometrics\BiometricsLogRepositoryInterface;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class BiometricsLogRepository implements BiometricsLogRepositoryInterface
{
    public function people(?string $search = null): Collection
    {
        return MirasolBiometricsLog::query()
            ->selectRaw("COALESCE(MIN(NULLIF(TRIM(source_employee_id), '')), CAST(MIN(employee_id) AS CHAR)) AS biometric_employee_id")
            ->selectRaw('TRIM(employee_no) AS employee_no')
            ->selectRaw("MIN(NULLIF(TRIM(employee_name), '')) AS employee_name")
            ->when(
                $search === null,
                fn (Builder $query) => $query->whereNotNull('employee_name')->whereRaw("TRIM(employee_name) <> ''"),
                fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                    ->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('employee_no', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('crosschex_id', 'like', "%{$search}%")
                    ->orWhere('source_employee_id', 'like', "%{$search}%"))
            )
            ->groupBy(DB::raw('TRIM(employee_no)'))
            ->get()
            ->toBase()
            ->map(fn (MirasolBiometricsLog $row): array => [
                'employee_no' => $row->employee_no,
                'employee_name' => $row->employee_name,
                'biometric_employee_id' => $row->getAttribute('biometric_employee_id'),
            ]);
    }

    public function forPeople(Collection $employeeNos, Collection $biometricIds, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return MirasolBiometricsLog::query()
            ->whereNotNull('check_time')
            ->whereBetween('check_time', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->where(function (Builder $query) use ($employeeNos, $biometricIds): void {
                if ($employeeNos->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('TRIM(employee_no)'), $employeeNos->all());
                }
                if ($biometricIds->isNotEmpty()) {
                    $query->orWhereIn(DB::raw('TRIM(source_employee_id)'), $biometricIds->all())
                        ->orWhereIn(DB::raw('TRIM(employee_id)'), $biometricIds->all());
                }
            })
            ->orderBy('employee_name')
            ->orderBy('check_time')
            ->get();
    }

    public function forIdentity(string $employeeNo, string $account, CarbonInterface $from, CarbonInterface $to, ?string $deviceSn = null): Collection
    {
        return MirasolBiometricsLog::query()
            ->where('employee_no', $employeeNo)
            ->where('crosschex_account', $account)
            ->whereBetween('check_time', [$from, $to])
            ->when($deviceSn !== null, fn (Builder $query) => $query->where('device_sn', $deviceSn))
            ->orderBy('check_time')
            ->get();
    }

    public function manualForDate(string $employeeNo, string $account, string $deviceSn, CarbonInterface $workDate): Collection
    {
        return MirasolBiometricsLog::query()
            ->where('employee_no', $employeeNo)
            ->where('crosschex_account', $account)
            ->where('device_sn', $deviceSn)
            ->where(fn (Builder $query) => $query
                ->whereDate('check_time', $workDate->toDateString())
                ->orWhere(fn (Builder $overnight) => $overnight
                    ->whereDate('check_time', $workDate->copy()->addDay()->toDateString())
                    ->where('state', 'Check Out')
                    ->where('raw->work_date', $workDate->toDateString())))
            ->get();
    }

    public function deleteMany(array $ids): void
    {
        if ($ids !== []) {
            MirasolBiometricsLog::query()->whereKey($ids)->delete();
        }
    }

    public function create(array $attributes): MirasolBiometricsLog
    {
        return MirasolBiometricsLog::query()->create($attributes);
    }
}
