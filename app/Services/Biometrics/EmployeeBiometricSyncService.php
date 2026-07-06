<?php

namespace App\Services\Biometrics;

use App\Models\EmployeeBiometric;
use App\Models\MirasolBiometricsLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeBiometricSyncService
{
    private array $columnCache = [];

    public function __construct(
        private readonly EmployeeBiometricIdentityService $identityService
    ) {}

    public function syncFromMirasol(): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $merged = 0;

        $people = $this->collectCanonicalPeople();

        DB::transaction(function () use ($people, &$created, &$updated, &$skipped, &$merged): void {
            foreach ($people as $person) {
                $employeeName = $this->identityService->clean($person['source_employee_name'] ?? null)
                    ?? $this->identityService->clean($person['source_crosschex_account_name'] ?? null);

                if ($employeeName === null && empty($person['source_employee_id']) && empty($person['source_employee_no'])) {
                    $skipped++;

                    continue;
                }

                $existing = $this->findExistingBiometric($person);

                $person['employee_identity_hash'] = $this->identityService->identityHash($person);
                $person['employment_status'] = $person['employment_status'] ?? 'active';
                $person['is_payroll_active'] = $person['is_payroll_active'] ?? true;

                if ($existing) {
                    $existing->update($this->mergePayload($existing, $person));
                    $updated++;

                    if (($person['merged_count'] ?? 1) > 1) {
                        $merged += ((int) $person['merged_count']) - 1;
                    }

                    continue;
                }

                EmployeeBiometric::create($person);
                $created++;

                if (($person['merged_count'] ?? 1) > 1) {
                    $merged += ((int) $person['merged_count']) - 1;
                }
            }
        });

        return compact('created', 'updated', 'skipped', 'merged');
    }

    private function collectCanonicalPeople(): Collection
    {
        $timeColumn = $this->biometricDateTimeColumn();
        $table = (new MirasolBiometricsLog)->getTable();

        $logs = MirasolBiometricsLog::query()
            ->select($this->existingColumns($table, [
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
            ]))
            ->orderByDesc($timeColumn)
            ->get();

        $people = collect();

        foreach ($logs as $log) {
            $payload = $this->payloadFromLog($log, $timeColumn);
            $identityKeys = $this->identityKeys($payload);

            if (empty($identityKeys)) {
                continue;
            }

            $existingKey = null;

            foreach ($people as $key => $person) {
                if (array_intersect($identityKeys, $person['_identity_keys'])) {
                    $existingKey = $key;
                    break;
                }
            }

            if ($existingKey === null) {
                $payload['_identity_keys'] = $identityKeys;
                $payload['merged_count'] = 1;
                $people->put($identityKeys[0], $payload);

                continue;
            }

            $existing = $people->get($existingKey);

            $people->put($existingKey, $this->mergeArrayPayload($existing, $payload));
        }

        return $people
            ->map(function (array $person): array {
                unset($person['_identity_keys']);

                return $person;
            })
            ->values();
    }

    private function payloadFromLog(MirasolBiometricsLog $log, string $timeColumn): array
    {
        $employeeId = $this->identityService->clean($log->employee_id ?? null);
        $employeeNo = $this->identityService->clean($log->employee_no ?? null);
        $employeeName = $this->identityService->clean($log->employee_name ?? null);
        $crosschexId = $this->identityService->clean($log->crosschex_id ?? null);
        $account = $this->identityService->clean($log->crosschex_account ?? null);
        $accountName = $this->identityService->clean($log->crosschex_account_name ?? null);
        $sourceKey = $employeeId ?: $crosschexId ?: $employeeNo ?: $employeeName ?: $accountName;

        return [
            'source_key' => $sourceKey,
            'biometric_company_id' => null,
            'display_employee_no' => $employeeNo ?: $employeeId ?: $crosschexId,
            'display_name' => $employeeName ?: $accountName ?: $account,
            'employment_status' => 'active',
            'group_name' => null,
            'is_payroll_active' => true,
            'source_crosschex_account' => $account,
            'source_crosschex_account_name' => $accountName,
            'source_crosschex_id' => $crosschexId,
            'source_employee_id' => $employeeId,
            'source_employee_no' => $employeeNo,
            'source_employee_name' => $employeeName,
            'device_sn' => $this->identityService->clean($log->device_sn ?? null),
            'device_name' => $this->identityService->clean($log->device_name ?? null),
            'last_check_time' => ! empty($log->{$timeColumn})
                ? Carbon::parse($log->{$timeColumn}, 'Asia/Manila')
                : null,
            'total_logs' => 1,
            'remarks' => null,
        ];
    }

    private function findExistingBiometric(array $person): ?EmployeeBiometric
    {
        $existing = null;

        $hash = $this->identityService->identityHash($person);

        if ($hash !== null) {
            $existing = EmployeeBiometric::query()
                ->where('employee_identity_hash', $hash)
                ->first();
        }

        if ($existing) {
            return $existing;
        }

        return $this->identityService->resolve(
            biometricEmployeeId: $person['source_employee_id'] ?? null,
            employeeNo: $person['source_employee_no'] ?? null,
            employeeName: $person['source_employee_name'] ?? $person['display_name'] ?? null,
            crosschexId: $person['source_crosschex_id'] ?? null
        );
    }

    private function mergePayload(EmployeeBiometric $existing, array $incoming): array
    {
        return [
            'employee_identity_hash' => $incoming['employee_identity_hash'] ?: $existing->employee_identity_hash,
            'source_key' => $existing->source_key ?: $incoming['source_key'],
            'display_employee_no' => $existing->display_employee_no ?: $incoming['display_employee_no'],
            'display_name' => $existing->display_name ?: $incoming['display_name'],
            'source_crosschex_account' => $existing->source_crosschex_account ?: $incoming['source_crosschex_account'],
            'source_crosschex_account_name' => $existing->source_crosschex_account_name ?: $incoming['source_crosschex_account_name'],
            'source_crosschex_id' => $existing->source_crosschex_id ?: $incoming['source_crosschex_id'],
            'source_employee_id' => $existing->source_employee_id ?: $incoming['source_employee_id'],
            'source_employee_no' => $existing->source_employee_no ?: $incoming['source_employee_no'],
            'source_employee_name' => $existing->source_employee_name ?: $incoming['source_employee_name'],
            'device_sn' => $incoming['device_sn'] ?: $existing->device_sn,
            'device_name' => $incoming['device_name'] ?: $existing->device_name,
            'last_check_time' => $this->latestDate($existing->last_check_time, $incoming['last_check_time'] ?? null),
            'total_logs' => max(0, (int) $existing->total_logs) + max(1, (int) ($incoming['total_logs'] ?? 1)),
        ];
    }

    private function mergeArrayPayload(array $existing, array $incoming): array
    {
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
        ] as $field) {
            $existing[$field] = $existing[$field] ?: ($incoming[$field] ?? null);
        }

        $existing['last_check_time'] = $this->latestDate($existing['last_check_time'] ?? null, $incoming['last_check_time'] ?? null);
        $existing['total_logs'] = (int) ($existing['total_logs'] ?? 0) + (int) ($incoming['total_logs'] ?? 1);
        $existing['merged_count'] = (int) ($existing['merged_count'] ?? 1) + 1;
        $existing['_identity_keys'] = collect(array_merge(
            $existing['_identity_keys'] ?? [],
            $this->identityKeys($incoming)
        ))->unique()->values()->all();

        return $existing;
    }

    private function identityKeys(array $payload): array
    {
        return collect([
            $payload['source_employee_id'] ? 'employee_id:'.mb_strtolower($payload['source_employee_id']) : null,
            $payload['source_employee_no'] ? 'employee_no:'.mb_strtolower($payload['source_employee_no']) : null,
            $payload['source_crosschex_id'] ? 'crosschex_id:'.mb_strtolower($payload['source_crosschex_id']) : null,
            $payload['source_employee_name'] ? 'employee_name:'.mb_strtolower($payload['source_employee_name']) : null,
            $payload['display_name'] ? 'display_name:'.mb_strtolower($payload['display_name']) : null,
        ])->filter()->values()->all();
    }

    private function latestDate(mixed $current, mixed $incoming): mixed
    {
        if ($current === null) {
            return $incoming;
        }

        if ($incoming === null) {
            return $current;
        }

        return Carbon::parse($incoming)->gt(Carbon::parse($current)) ? $incoming : $current;
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

    private function existingColumns(string $table, array $columns): array
    {
        return collect($columns)
            ->filter(fn (string $column) => $this->columnExists($table, $column))
            ->unique()
            ->values()
            ->toArray();
    }

    private function columnExists(string $table, string $column): bool
    {
        $key = $table.'.'.$column;

        if (array_key_exists($key, $this->columnCache)) {
            return $this->columnCache[$key];
        }

        try {
            $this->columnCache[$key] = Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            $this->columnCache[$key] = false;
        }

        return $this->columnCache[$key];
    }
}
