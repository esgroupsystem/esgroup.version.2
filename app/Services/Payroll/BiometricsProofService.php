<?php

namespace App\Services\Payroll;

use App\Models\MirasolBiometricsLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class BiometricsProofService
{
    public function findOffsetProof(
        string $biometricEmployeeId,
        ?string $employeeNo,
        string $employeeName,
        string $offsetSourceDate
    ): ?array {
        $model = new MirasolBiometricsLog;
        $table = $model->getTable();

        $timeColumn = $this->biometricDateTimeColumn();

        $startDateTime = Carbon::parse($offsetSourceDate)->startOfDay();
        $endDateTime = Carbon::parse($offsetSourceDate)->endOfDay();

        $identityFilters = $this->buildIdentityFilters(
            $table,
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
            ->where(function ($query) use ($identityFilters) {
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

                return $value ? Carbon::parse($value) : null;
            })
            ->filter()
            ->sort()
            ->values();

        if ($times->isEmpty()) {
            return null;
        }

        $firstLog = $logs->first();

        return [
            'date' => Carbon::parse($offsetSourceDate)->format('Y-m-d'),
            'employee_name' => $this->firstFilledValue([
                $firstLog->employee_name ?? null,
                $firstLog->crosschex_account_name ?? null,
                $firstLog->crosschex_account ?? null,
                $employeeName,
            ]),
            'employee_no' => $this->firstFilledValue([
                $firstLog->employee_no ?? null,
                $firstLog->employee_id ?? null,
                $firstLog->crosschex_id ?? null,
                $employeeNo,
            ]),
            'biometric_employee_id' => $this->firstFilledValue([
                $firstLog->employee_no ?? null,
                $firstLog->employee_id ?? null,
                $firstLog->crosschex_id ?? null,
                $biometricEmployeeId,
            ]),
            'time_in' => $times->first()->format('H:i'),
            'time_out' => $times->last()->format('H:i'),
            'count' => $logs->count(),
            'logs' => $logs->map(function (MirasolBiometricsLog $log) use ($timeColumn) {
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
                    ]),
                    'check_time' => $checkTime
                        ? Carbon::parse($checkTime)->format('Y-m-d H:i:s')
                        : null,
                    'state' => $log->state ?? null,
                    'device_name' => $log->device_name ?? null,
                ];
            })->values()->toArray(),
        ];
    }

    private function buildIdentityFilters(
        string $table,
        string $biometricEmployeeId,
        ?string $employeeNo,
        string $employeeName
    ): array {
        $filters = [];

        $employeeNo = trim((string) $employeeNo);
        $biometricEmployeeId = trim($biometricEmployeeId);
        $employeeName = trim($employeeName);

        foreach (['employee_no', 'employee_id', 'crosschex_id'] as $column) {
            if (Schema::hasColumn($table, $column) && $employeeNo !== '') {
                $filters[] = [
                    'column' => $column,
                    'value' => $employeeNo,
                ];
            }

            if (Schema::hasColumn($table, $column) && $biometricEmployeeId !== '') {
                $filters[] = [
                    'column' => $column,
                    'value' => $biometricEmployeeId,
                ];
            }
        }

        foreach (['employee_name', 'crosschex_account_name', 'crosschex_account'] as $column) {
            if (Schema::hasColumn($table, $column) && $employeeName !== '') {
                $filters[] = [
                    'column' => $column,
                    'value' => $employeeName,
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
            if ($value === null) {
                continue;
            }

            $value = trim((string) $value);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
