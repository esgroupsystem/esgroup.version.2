<?php

namespace App\Services\Biometrics;

use App\Models\EmployeeBiometric;
use App\Models\MirasolBiometricsLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class EmployeeBiometricSyncService
{
    private array $columnCache = [];

    public function __construct(
        private readonly EmployeeBiometricIdentityService $identityService
    ) {}

    /**
     * Synchronize every CrossChex account stored in the biometric logs table.
     */
    public function syncAllAccounts(): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $merged = 0;

        $people = $this->collectCanonicalPeople();

        DB::transaction(function () use (
            $people,
            &$created,
            &$updated,
            &$skipped,
            &$merged
        ): void {
            foreach ($people as $person) {
                $mergedCount = max(
                    1,
                    (int) ($person['merged_count'] ?? 1)
                );

                unset(
                    $person['merged_count'],
                    $person['_identity_keys']
                );

                $employeeName = $this->identityService->clean(
                    $person['source_employee_name'] ?? null
                ) ?? $this->identityService->clean(
                    $person['source_crosschex_account_name'] ?? null
                );

                $employeeId = $this->identityService->clean(
                    $person['source_employee_id'] ?? null
                );

                $employeeNo = $this->identityService->clean(
                    $person['source_employee_no'] ?? null
                );

                if (
                    $employeeName === null
                    && $employeeId === null
                    && $employeeNo === null
                ) {
                    $skipped++;

                    continue;
                }

                $person['employment_status'] = $person['employment_status']
                    ?? EmployeeBiometric::STATUS_ACTIVE;

                $person['is_payroll_active'] = $person['is_payroll_active']
                    ?? true;

                $person['group_name'] = $person['group_name']
                    ?? $this->defaultGroupName(
                        $person['source_crosschex_account'] ?? null
                    );

                $person['employee_identity_hash'] =
                    $this->identityService->identityHash($person);

                $existing = $this->findExistingBiometric($person);

                if ($existing !== null) {
                    $existing->update(
                        $this->mergePayload($existing, $person)
                    );

                    $updated++;
                    $merged += $mergedCount - 1;

                    continue;
                }

                try {
                    EmployeeBiometric::query()->create($person);

                    $created++;
                    $merged += $mergedCount - 1;
                } catch (UniqueConstraintViolationException $exception) {
                    /*
                     * Handles two sync requests executing at the same time.
                     * Re-query the newly created record and update it instead.
                     */
                    $existing = $this->findExistingBySource($person);

                    if ($existing === null) {
                        throw $exception;
                    }

                    $existing->update(
                        $this->mergePayload($existing, $person)
                    );

                    $updated++;
                    $merged += $mergedCount - 1;
                }
            }
        }, 3);

        return compact(
            'created',
            'updated',
            'skipped',
            'merged'
        );
    }

    /**
     * Backward-compatible method for existing controller calls.
     */
    public function syncFromMirasol(): array
    {
        return $this->syncAllAccounts();
    }

    private function collectCanonicalPeople(): Collection
    {
        $timeColumn = $this->biometricDateTimeColumn();
        $table = (new MirasolBiometricsLog)->getTable();

        $logs = MirasolBiometricsLog::query()
            ->select(
                $this->existingColumns($table, [
                    'id',
                    'employee_id',
                    'employee_no',
                    'employee_name',
                    'crosschex_id',
                    'crosschex_account',
                    'crosschex_account_name',
                    'device_sn',
                    'device_name',
                    $timeColumn,
                ])
            )
            ->orderByDesc($timeColumn)
            ->orderByDesc('id')
            ->cursor();

        $people = collect();
        $identityIndex = [];

        foreach ($logs as $log) {
            $payload = $this->payloadFromLog(
                $log,
                $timeColumn
            );

            $identityKeys = $this->identityKeys($payload);

            if ($identityKeys === []) {
                continue;
            }

            $canonicalKey = null;

            foreach ($identityKeys as $identityKey) {
                if (isset($identityIndex[$identityKey])) {
                    $canonicalKey = $identityIndex[$identityKey];

                    break;
                }
            }

            if ($canonicalKey === null) {
                $canonicalKey = $identityKeys[0];

                $payload['_identity_keys'] = $identityKeys;
                $payload['merged_count'] = 1;

                $people->put($canonicalKey, $payload);

                foreach ($identityKeys as $identityKey) {
                    $identityIndex[$identityKey] = $canonicalKey;
                }

                continue;
            }

            $existing = $people->get($canonicalKey);

            if (! is_array($existing)) {
                continue;
            }

            $mergedPayload = $this->mergeArrayPayload(
                $existing,
                $payload
            );

            $people->put($canonicalKey, $mergedPayload);

            foreach (
                $mergedPayload['_identity_keys'] ?? [] as $identityKey
            ) {
                $identityIndex[$identityKey] = $canonicalKey;
            }
        }

        return $people
            ->map(function (array $person): array {
                unset($person['_identity_keys']);

                return $person;
            })
            ->values();
    }

    private function payloadFromLog(
        MirasolBiometricsLog $log,
        string $timeColumn
    ): array {
        $account = $this->identityService->clean(
            $log->crosschex_account ?? null
        );

        $accountName = $this->identityService->clean(
            $log->crosschex_account_name ?? null
        );

        $employeeId = $this->identityService->clean(
            $log->employee_id ?? null
        );

        $employeeNo = $this->identityService->clean(
            $log->employee_no ?? null
        );

        $employeeName = $this->identityService->clean(
            $log->employee_name ?? null
        );

        /*
         * crosschex_id from the logs is the attendance transaction UUID.
         * The employee UUID retrieved by the API is stored in employee_id.
         */
        $sourceEmployeeIdentifier = $employeeId;

        return [
            /*
             * The source key includes the identifier type.
             *
             * employee_id:1 and employee_no:1 must not be treated as the
             * same source identity inside one CrossChex account.
             */
            'source_key' => $this->buildSourceKey(
                employeeId: $employeeId,
                employeeNo: $employeeNo,
                employeeName: $employeeName,
                accountName: $accountName
            ),

            'biometric_company_id' => null,

            'display_employee_no' => $employeeNo
                ?: $employeeId,

            'display_name' => $employeeName
                ?: $accountName
                ?: $account,

            'employment_status' => EmployeeBiometric::STATUS_ACTIVE,

            'group_name' => $this->defaultGroupName($account),

            'is_payroll_active' => true,

            'source_crosschex_account' => $account,
            'source_crosschex_account_name' => $accountName,

            /*
             * Store the employee UUID, not the attendance record UUID.
             */
            'source_crosschex_id' => $sourceEmployeeIdentifier,

            'source_employee_id' => $employeeId,
            'source_employee_no' => $employeeNo,
            'source_employee_name' => $employeeName,

            'device_sn' => $this->identityService->clean(
                $log->device_sn ?? null
            ),

            'device_name' => $this->identityService->clean(
                $log->device_name ?? null
            ),

            'last_check_time' => $this->parseLogTime(
                $log->{$timeColumn} ?? null
            ),

            'total_logs' => 1,
            'remarks' => null,
        ];
    }

    private function buildSourceKey(
        ?string $employeeId,
        ?string $employeeNo,
        ?string $employeeName,
        ?string $accountName
    ): ?string {
        $identifiers = [
            'employee_id' => $employeeId,
            'employee_no' => $employeeNo,
            'employee_name' => $employeeName,
            'account_name' => $accountName,
        ];

        foreach ($identifiers as $type => $value) {
            $value = $this->identityService->clean($value);

            if ($value === null) {
                continue;
            }

            /*
             * Fixed-length hashed value prevents oversized database indexes
             * while retaining the identifier type.
             */
            return $type.':'.hash(
                'sha256',
                mb_strtolower($value)
            );
        }

        return null;
    }

    private function findExistingBiometric(
        array $person
    ): ?EmployeeBiometric {
        /*
         * First priority: exact account and source-key combination.
         */
        $existing = $this->findExistingBySource($person);

        if ($existing !== null) {
            return $existing;
        }

        /*
         * Second priority: account-scoped identity hash.
         */
        $hash = $this->identityService->identityHash($person);

        if ($hash !== null) {
            $query = EmployeeBiometric::query()
                ->where('employee_identity_hash', $hash);

            $this->applyAccountScope(
                $query,
                $person['source_crosschex_account'] ?? null
            );

            $existing = $query
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                return $existing;
            }
        }

        /*
         * Third priority: account-scoped source identifiers.
         */
        $resolved = $this->identityService->resolve(
            biometricEmployeeId: $person['source_employee_id'] ?? null,
            employeeNo: $person['source_employee_no'] ?? null,
            employeeName: $person['source_employee_name']
                ?? $person['display_name']
                ?? null,
            crosschexId: $person['source_crosschex_id'] ?? null,
            onlyPayrollActive: false,
            crosschexAccount: $person['source_crosschex_account']
                ?? null
        );

        if ($resolved === null) {
            return null;
        }

        return EmployeeBiometric::query()
            ->lockForUpdate()
            ->find($resolved->id);
    }

    private function findExistingBySource(
        array $person
    ): ?EmployeeBiometric {
        $sourceKey = $this->identityService->clean(
            $person['source_key'] ?? null
        );

        if ($sourceKey === null) {
            return null;
        }

        $query = EmployeeBiometric::query()
            ->where('source_key', $sourceKey);

        $this->applyAccountScope(
            $query,
            $person['source_crosschex_account'] ?? null
        );

        return $query
            ->lockForUpdate()
            ->first();
    }

    private function applyAccountScope(
        Builder $query,
        mixed $account
    ): void {
        $account = $this->identityService->clean($account);

        if ($account === null) {
            $query->whereNull('source_crosschex_account');

            return;
        }

        $query->where(
            'source_crosschex_account',
            $account
        );
    }

    private function mergePayload(
        EmployeeBiometric $existing,
        array $incoming
    ): array {
        return [
            /*
             * Source-controlled fields are refreshed.
             */
            'employee_identity_hash' => $incoming['employee_identity_hash']
                ?: $existing->employee_identity_hash,

            'source_key' => $incoming['source_key']
                ?: $existing->source_key,

            'source_crosschex_account' => $incoming['source_crosschex_account']
                ?: $existing->source_crosschex_account,

            'source_crosschex_account_name' => $incoming['source_crosschex_account_name']
                ?: $existing->source_crosschex_account_name,

            'source_crosschex_id' => $incoming['source_crosschex_id']
                ?: $existing->source_crosschex_id,

            'source_employee_id' => $incoming['source_employee_id']
                ?: $existing->source_employee_id,

            'source_employee_no' => $incoming['source_employee_no']
                ?: $existing->source_employee_no,

            'source_employee_name' => $incoming['source_employee_name']
                ?: $existing->source_employee_name,

            'device_sn' => $incoming['device_sn']
                ?: $existing->device_sn,

            'device_name' => $incoming['device_name']
                ?: $existing->device_name,

            /*
             * Manual display fields are preserved.
             */
            'display_employee_no' => $existing->display_employee_no
                ?: $incoming['display_employee_no'],

            'display_name' => $existing->display_name
                ?: $incoming['display_name'],

            'last_check_time' => $this->latestDate(
                $existing->last_check_time,
                $incoming['last_check_time'] ?? null
            ),

            /*
             * The logs table is read as a complete source. Do not add the
             * previous count during every sync or total_logs will inflate.
             */
            'total_logs' => max(
                1,
                (int) ($incoming['total_logs'] ?? 1)
            ),
        ];
    }

    private function mergeArrayPayload(
        array $existing,
        array $incoming
    ): array {
        /*
         * Logs are ordered newest first. Preserve the first non-empty source
         * values while accumulating the total number of logs.
         */
        foreach ([
            'source_key',
            'display_employee_no',
            'display_name',
            'source_crosschex_account',
            'source_crosschex_account_name',
            'source_crosschex_id',
            'source_employee_id',
            'source_employee_no',
            'source_employee_name',
            'device_sn',
            'device_name',
            'group_name',
        ] as $field) {
            $existing[$field] = $existing[$field]
                ?: ($incoming[$field] ?? null);
        }

        $existing['last_check_time'] = $this->latestDate(
            $existing['last_check_time'] ?? null,
            $incoming['last_check_time'] ?? null
        );

        $existing['total_logs'] =
            (int) ($existing['total_logs'] ?? 0)
            + (int) ($incoming['total_logs'] ?? 1);

        $existing['merged_count'] =
            (int) ($existing['merged_count'] ?? 1) + 1;

        $existing['_identity_keys'] = collect(
            array_merge(
                $existing['_identity_keys'] ?? [],
                $this->identityKeys($incoming)
            )
        )
            ->unique()
            ->values()
            ->all();

        return $existing;
    }

    private function identityKeys(array $payload): array
    {
        $account = $this->identityService->clean(
            $payload['source_crosschex_account'] ?? null
        ) ?: 'legacy';

        $scope = 'account:'.mb_strtolower($account);

        $employeeId = $this->identityService->clean(
            $payload['source_employee_id'] ?? null
        );

        $employeeNo = $this->identityService->clean(
            $payload['source_employee_no'] ?? null
        );

        $keys = collect([
            $employeeId !== null
                ? $scope.'|employee_id:'.mb_strtolower($employeeId)
                : null,

            $employeeNo !== null
                ? $scope.'|employee_no:'.mb_strtolower($employeeNo)
                : null,
        ])
            ->filter()
            ->values();

        /*
         * Names are used only when no stable ID or employee number exists.
         */
        if ($keys->isEmpty()) {
            $employeeName = $this->identityService->clean(
                $payload['source_employee_name']
                    ?? $payload['display_name']
                    ?? null
            );

            if ($employeeName !== null) {
                $keys->push(
                    $scope
                    .'|employee_name:'
                    .mb_strtolower($employeeName)
                );
            }
        }

        return $keys
            ->unique()
            ->values()
            ->all();
    }

    private function defaultGroupName(
        mixed $account
    ): string|int|null {
        $account = mb_strtolower(
            $this->identityService->clean($account) ?? ''
        );

        return match ($account) {
            'mirasol' => EmployeeBiometric::PAYROLL_GROUP_MIRASOL,
            'gonzales' => EmployeeBiometric::PAYROLL_GROUP_GONZALES,
            default => null,
        };
    }

    private function parseLogTime(
        mixed $value
    ): ?Carbon {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse(
                $value,
                config('app.timezone', 'Asia/Manila')
            );
        } catch (Throwable) {
            return null;
        }
    }

    private function latestDate(
        mixed $current,
        mixed $incoming
    ): mixed {
        if ($current === null) {
            return $incoming;
        }

        if ($incoming === null) {
            return $current;
        }

        return Carbon::parse($incoming)
            ->gt(Carbon::parse($current))
                ? $incoming
                : $current;
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
            if ($this->columnExists($table, $column)) {
                return $column;
            }
        }

        return 'created_at';
    }

    private function existingColumns(
        string $table,
        array $columns
    ): array {
        return collect($columns)
            ->filter(
                fn (string $column): bool => $this->columnExists($table, $column)
            )
            ->unique()
            ->values()
            ->all();
    }

    private function columnExists(
        string $table,
        string $column
    ): bool {
        $key = $table.'.'.$column;

        if (array_key_exists($key, $this->columnCache)) {
            return $this->columnCache[$key];
        }

        try {
            $this->columnCache[$key] =
                Schema::hasColumn($table, $column);
        } catch (Throwable) {
            $this->columnCache[$key] = false;
        }

        return $this->columnCache[$key];
    }
}
