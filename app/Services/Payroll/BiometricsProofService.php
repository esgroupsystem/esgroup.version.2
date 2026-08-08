<?php

namespace App\Services\Payroll;

use App\Models\EmployeeBiometric;
use App\Models\MirasolBiometricsLog;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class BiometricsProofService
{
    public function __construct(
        private readonly EmployeeBiometricIdentityService $identityService
    ) {}

    /**
     * Return Offset source biometrics for ONE canonical employee only.
     *
     * Important identity rule:
     * - employee_biometrics.id is the authoritative local identity.
     * - CrossChex account is a SCOPE/BORDER, never a person identifier.
     * - mirasol_biometrics_logs.crosschex_id is an attendance transaction UUID,
     *   therefore it must never be compared with an employee UUID/employee no.
     *
     * The old implementation OR'ed account/account-name values together with
     * employee identifiers. An account value such as "Mirasol Biometrics"
     * therefore matched every employee log in that account and produced the
     * random 100-row Offset proof shown in the UI.
     */
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

        $selectColumns = $this->existingColumns($table, [
            'id',
            'crosschex_account',
            'crosschex_account_name',
            'employee_id',
            'employee_no',
            'crosschex_id',
            'employee_name',
            'state',
            'device_sn',
            'device_name',
            $timeColumn,
        ]);

        $query = MirasolBiometricsLog::query()
            ->select($selectColumns)
            ->whereBetween($timeColumn, [
                $startDateTime->toDateTimeString(),
                $endDateTime->toDateTimeString(),
            ]);

        $match = $this->applyCanonicalEmployeeScope(
            query: $query,
            table: $table,
            employee: $employee,
            fallbackBiometricEmployeeId: $biometricEmployeeId,
            fallbackEmployeeNo: $employeeNo,
            fallbackEmployeeName: $employeeName,
        );

        if (! $match['matched']) {
            return null;
        }

        $logs = $query
            ->orderBy($timeColumn)
            ->orderBy('id')
            ->limit(500)
            ->get();

        if ($logs->isEmpty()) {
            return null;
        }

        $times = $logs
            ->map(function (MirasolBiometricsLog $log) use ($timeColumn): ?Carbon {
                $value = $log->{$timeColumn};

                return $value ? Carbon::parse($value, 'Asia/Manila') : null;
            })
            ->filter()
            ->sort()
            ->values();

        if ($times->isEmpty()) {
            return null;
        }

        $snapshot = $this->identityService->snapshot($employee);

        return [
            'date' => Carbon::parse($offsetSourceDate, 'Asia/Manila')->format('Y-m-d'),
            'employee_biometric_id' => $employee->id,
            'employee_name' => $snapshot['employee_name'],
            'employee_no' => $snapshot['employee_no'],
            'biometric_employee_id' => $snapshot['biometric_employee_id'],
            'crosschex_account' => $match['account'],
            'identity_match' => $match['strategy'],
            'time_in' => $times->first()->format('H:i'),
            'time_out' => $times->last()->format('H:i'),
            'count' => $logs->count(),
            'logs' => $logs->map(function (MirasolBiometricsLog $log) use ($timeColumn): array {
                $checkTime = $log->{$timeColumn};

                return [
                    'id' => $log->id ?? null,
                    'crosschex_account' => $log->crosschex_account ?? null,
                    'employee_id' => $log->employee_id ?? null,
                    'employee_no' => $log->employee_no ?? null,
                    'crosschex_id' => $log->crosschex_id ?? null,
                    'employee_name' => $this->firstFilledValue([
                        $log->employee_name ?? null,
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

    /**
     * Apply a deterministic ONE-employee filter to Mirasol biometric logs.
     *
     * Priority:
     *  1. CrossChex account boundary (AND scope, when available)
     *  2. source_employee_id -> logs.employee_id
     *  3. source_employee_no -> logs.employee_no
     *  4. legacy/request IDs only when canonical source fields are missing
     *  5. exact employee name only as a last resort
     *
     * We deliberately do not query logs.crosschex_id as an employee identity;
     * that column stores the attendance transaction UUID in this project.
     * We also deliberately do not use crosschex_account_name as an employee
     * name because it identifies an account/company, not a person.
     *
     * @return array{matched: bool, strategy: string, account: ?string}
     */
    private function applyCanonicalEmployeeScope(
        Builder $query,
        string $table,
        EmployeeBiometric $employee,
        ?string $fallbackBiometricEmployeeId,
        ?string $fallbackEmployeeNo,
        string $fallbackEmployeeName
    ): array {
        $account = $this->identityService->clean(
            $employee->source_crosschex_account ?? null
        );

        if ($account !== null && Schema::hasColumn($table, 'crosschex_account')) {
            $query->whereRaw('TRIM(crosschex_account) = ?', [$account]);
        }

        $sourceEmployeeId = $this->identityService->clean(
            $employee->source_employee_id ?? null
        );

        if ($sourceEmployeeId !== null && Schema::hasColumn($table, 'employee_id')) {
            $query->where('employee_id', $sourceEmployeeId);

            return [
                'matched' => true,
                'strategy' => 'canonical employee_id'.($account !== null ? ' + CrossChex account' : ''),
                'account' => $account,
            ];
        }

        $sourceEmployeeNo = $this->identityService->clean(
            $employee->source_employee_no ?? null
        );

        if ($sourceEmployeeNo !== null && Schema::hasColumn($table, 'employee_no')) {
            $query->whereRaw('TRIM(employee_no) = ?', [$sourceEmployeeNo]);

            return [
                'matched' => true,
                'strategy' => 'canonical employee_no'.($account !== null ? ' + CrossChex account' : ''),
                'account' => $account,
            ];
        }

        /*
         * Legacy fallback is allowed only when the canonical employee record
         * does not contain the corresponding source identifier.
         */
        $fallbackBiometricEmployeeId = $this->identityService->clean(
            $fallbackBiometricEmployeeId
        );

        if ($fallbackBiometricEmployeeId !== null && Schema::hasColumn($table, 'employee_id')) {
            $query->where('employee_id', $fallbackBiometricEmployeeId);

            return [
                'matched' => true,
                'strategy' => 'legacy employee_id'.($account !== null ? ' + CrossChex account' : ''),
                'account' => $account,
            ];
        }

        $displayEmployeeNo = $this->identityService->clean(
            $employee->display_employee_no ?? null
        );
        $fallbackEmployeeNo = $this->identityService->clean($fallbackEmployeeNo);
        $employeeNo = $displayEmployeeNo ?? $fallbackEmployeeNo;

        if ($employeeNo !== null && Schema::hasColumn($table, 'employee_no')) {
            $query->whereRaw('TRIM(employee_no) = ?', [$employeeNo]);

            return [
                'matched' => true,
                'strategy' => 'fallback employee_no'.($account !== null ? ' + CrossChex account' : ''),
                'account' => $account,
            ];
        }

        $canonicalName = $this->firstFilledValue([
            $employee->source_employee_name ?? null,
            $employee->display_name ?? null,
            $fallbackEmployeeName,
        ]);

        if ($canonicalName !== null && Schema::hasColumn($table, 'employee_name')) {
            $query->whereRaw(
                'LOWER(TRIM(employee_name)) = ?',
                [mb_strtolower($canonicalName)]
            );

            return [
                'matched' => true,
                'strategy' => 'exact employee_name fallback'.($account !== null ? ' + CrossChex account' : ''),
                'account' => $account,
            ];
        }

        $query->whereRaw('1 = 0');

        return [
            'matched' => false,
            'strategy' => 'no safe employee identity available',
            'account' => $account,
        ];
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
