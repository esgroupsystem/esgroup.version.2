<?php

namespace App\Services\Payroll;

use App\Models\EmployeeBiometric;
use App\Models\MirasolBiometricsLog;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class BiometricsProofService
{
    public function __construct(
        private readonly EmployeeBiometricIdentityService $identityService
    ) {}

    public function findOffsetProof(
        int $employeeBiometricId,
        ?string $biometricEmployeeId,
        ?string $employeeNo,
        string $employeeName,
        string $offsetSourceDate
    ): ?array {
        $employee = EmployeeBiometric::query()->find($employeeBiometricId);

        if (! $employee) {
            return null;
        }

        $table = (new MirasolBiometricsLog)->getTable();
        $timeColumn = $this->biometricDateTimeColumn();

        $startDateTime = Carbon::parse($offsetSourceDate, 'Asia/Manila')->startOfDay();
        $endDateTime = Carbon::parse($offsetSourceDate, 'Asia/Manila')->endOfDay();

        $identityFilters = $this->buildIdentityFilters(
            $table,
            $employee,
            $biometricEmployeeId,
            $employeeNo,
            $employeeName
        );

        if (empty($identityFilters)) {
            return null;
        }

        $selectColumns = $this->existingColumns($table, [
            'id',
            'employee_id',
            'employee_no',
            'crosschex_id',
            'employee_name',
            'crosschex_account_name',
            'crosschex_account',
            'state',
            'device_name',
            $timeColumn,
        ]);

        $logs = MirasolBiometricsLog::query()
            ->select($selectColumns)
            ->whereBetween($timeColumn, [
                $startDateTime->toDateTimeString(),
                $endDateTime->toDateTimeString(),
            ])
            ->where(function ($query) use ($identityFilters): void {
                foreach ($identityFilters as $filter) {
                    $query->orWhere($filter['column'], $filter['value']);
                }
            })
            ->orderBy($timeColumn)
            ->limit(100)
            ->get();

        if ($logs->isEmpty()) {
            return null;
        }

        $times = $logs
            ->map(function (MirasolBiometricsLog $log) use ($timeColumn) {
                $value = $log->{$timeColumn};

                return $value ? Carbon::parse($value, 'Asia/Manila') : null;
            })
            ->filter()
            ->sort()
            ->values();

        if ($times->isEmpty()) {
            return null;
        }

        $firstLog = $logs->first();
        $snapshot = $this->identityService->snapshot($employee);

        return [
            'date' => Carbon::parse($offsetSourceDate, 'Asia/Manila')->format('Y-m-d'),
            'employee_biometric_id' => $employee->id,
            'employee_name' => $snapshot['employee_name'],
            'employee_no' => $snapshot['employee_no'],
            'biometric_employee_id' => $snapshot['biometric_employee_id'],
            'time_in' => $times->first()->format('H:i'),
            'time_out' => $times->last()->format('H:i'),
            'count' => $logs->count(),
            'logs' => $logs->map(function (MirasolBiometricsLog $log) use ($timeColumn, $firstLog) {
                $checkTime = $log->{$timeColumn};

                return [
                    'id' => $log->id ?? null,
                    'employee_id' => $log->employee_id ?? null,
                    'employee_no' => $log->employee_no ?? null,
                    'crosschex_id' => $log->crosschex_id ?? null,
                    'employee_name' => $this->firstFilledValue([
                        $log->employee_name ?? null,
                        $log->crosschex_account_name ?? null,
                        $log->crosschex_account ?? null,
                        $firstLog->employee_name ?? null,
                    ]),
                    'check_time' => $checkTime
                        ? Carbon::parse($checkTime, 'Asia/Manila')->format('Y-m-d H:i:s')
                        : null,
                    'state' => $log->state ?? null,
                    'device_name' => $log->device_name ?? null,
                ];
            })->values()->toArray(),
        ];
    }

    private function buildIdentityFilters(
        string $table,
        EmployeeBiometric $employee,
        ?string $biometricEmployeeId,
        ?string $employeeNo,
        string $employeeName
    ): array {
        $snapshot = $this->identityService->snapshot($employee);

        $identifierValues = collect([
            $employee->source_employee_id,
            $employee->source_employee_no,
            $employee->source_crosschex_id,
            $employee->source_crosschex_account,
            $employee->source_key,
            $snapshot['biometric_employee_id'],
            $snapshot['employee_no'],
            $biometricEmployeeId,
            $employeeNo,
        ])
            ->map(fn ($value) => $this->identityService->clean($value))
            ->filter()
            ->unique()
            ->values();

        $nameValues = collect([
            $employee->display_name,
            $employee->source_employee_name,
            $employee->source_crosschex_account_name,
            $employee->source_crosschex_account,
            $snapshot['employee_name'],
            $employeeName,
        ])
            ->map(fn ($value) => $this->identityService->clean($value))
            ->filter()
            ->unique()
            ->values();

        $filters = [];

        foreach (['employee_no', 'employee_id', 'crosschex_id'] as $column) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            foreach ($identifierValues as $value) {
                $filters[] = [
                    'column' => $column,
                    'value' => $value,
                ];
            }
        }

        foreach (['employee_name', 'crosschex_account_name', 'crosschex_account'] as $column) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            foreach ($nameValues as $value) {
                $filters[] = [
                    'column' => $column,
                    'value' => $value,
                ];
            }
        }

        return collect($filters)
            ->unique(fn (array $filter) => $filter['column'].'|'.$filter['value'])
            ->values()
            ->toArray();
    }

    private function biometricDateTimeColumn(): string
    {
        $table = (new MirasolBiometricsLog)->getTable();

        foreach ([
            'check_time',
            'log_datetime',
            'attendance_datetime',
            'punch_time',
            'scan_time',
            'recorded_at',
            'datetime',
            'date_time',
            'created_at',
        ] as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return 'created_at';
    }

    private function existingColumns(string $table, array $columns): array
    {
        return collect($columns)
            ->filter(fn (string $column) => Schema::hasColumn($table, $column))
            ->unique()
            ->values()
            ->toArray();
    }

    private function firstFilledValue(array $values): ?string
    {
        foreach ($values as $value) {
            $value = $this->identityService->clean($value);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }
}
