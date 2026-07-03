<?php

namespace App\Services\Biometrics;

use App\Models\EmployeeBiometric;
use App\Models\MirasolBiometricsLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeBiometricSyncService
{
    /**
     * Sync unique biometric employees from mirasol_biometrics_logs
     * into employee_biometrics.
     *
     * This service does NOT connect to the main employees table.
     *
     * Duplicate rule:
     * - Same source_employee_id = one biometric employee
     * - Same source_employee_no = one biometric employee
     * - Same source_employee_name = one biometric employee
     *
     * @return array<string, int>
     */
    public function syncFromMirasol(): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $merged = 0;

        $merged += $this->deduplicateExistingRecords();

        $table = (new MirasolBiometricsLog)->getTable();

        $baseQuery = DB::table($table)
            ->select([
                'crosschex_account',
                'crosschex_account_name',
                'crosschex_id',
                'employee_id',
                'employee_no',
                'employee_name',
                'device_sn',
                'device_name',
                'check_time',
            ])
            ->selectRaw($this->employeeIdentitySql().' AS employee_identity_key')
            ->whereRaw("
                COALESCE(
                    NULLIF(TRIM(CAST(employee_id AS CHAR)), ''),
                    NULLIF(TRIM(CAST(employee_no AS CHAR)), ''),
                    NULLIF(TRIM(employee_name), ''),
                    NULLIF(TRIM(CAST(crosschex_id AS CHAR)), '')
                ) IS NOT NULL
            ");

        DB::query()
            ->fromSub($baseQuery, 'bio')
            ->select([
                'bio.employee_identity_key',
            ])
            ->selectRaw('MAX(bio.crosschex_account) AS crosschex_account')
            ->selectRaw('MAX(bio.crosschex_account_name) AS crosschex_account_name')
            ->selectRaw('MAX(CAST(bio.crosschex_id AS CHAR)) AS crosschex_id')
            ->selectRaw('MAX(CAST(bio.employee_id AS CHAR)) AS employee_id')
            ->selectRaw('MAX(CAST(bio.employee_no AS CHAR)) AS employee_no')
            ->selectRaw('MAX(bio.employee_name) AS employee_name')
            ->selectRaw('MAX(bio.device_sn) AS device_sn')
            ->selectRaw('MAX(bio.device_name) AS device_name')
            ->selectRaw('MAX(bio.check_time) AS last_check_time')
            ->selectRaw('COUNT(*) AS total_logs')
            ->groupBy('bio.employee_identity_key')
            ->orderBy('bio.employee_identity_key')
            ->chunk(500, function ($rows) use (&$created, &$updated, &$skipped, &$merged): void {
                foreach ($rows as $row) {
                    DB::transaction(function () use ($row, &$created, &$updated, &$skipped, &$merged): void {
                        $employeeIdentityKey = $this->nullableString($row->employee_identity_key);

                        if ($employeeIdentityKey === null) {
                            $skipped++;

                            return;
                        }

                        $sourceKey = $this->makeSourceKey($employeeIdentityKey);

                        $payload = [
                            'source_key' => $sourceKey,
                            'source_crosschex_account' => $this->nullableString($row->crosschex_account),
                            'source_crosschex_account_name' => $this->nullableString($row->crosschex_account_name),
                            'source_crosschex_id' => $this->nullableString($row->crosschex_id),
                            'source_employee_id' => $this->nullableString($row->employee_id),
                            'source_employee_no' => $this->nullableString($row->employee_no),
                            'source_employee_name' => $this->nullableString($row->employee_name),
                            'device_sn' => $this->nullableString($row->device_sn),
                            'device_name' => $this->nullableString($row->device_name),
                            'last_check_time' => $row->last_check_time
                                ? Carbon::parse($row->last_check_time)
                                : null,
                            'total_logs' => (int) $row->total_logs,
                        ];

                        $employeeBiometric = $this->resolveExistingEmployeeBiometric(
                            $sourceKey,
                            $payload,
                            $merged
                        );

                        if (! $employeeBiometric) {
                            EmployeeBiometric::query()->create(array_merge($payload, [
                                'display_employee_no' => $payload['source_employee_no'],
                                'display_name' => $payload['source_employee_name']
                                    ?: $payload['source_employee_no']
                                    ?: $payload['source_employee_id']
                                    ?: 'Unknown Biometric Employee',
                                'employment_status' => EmployeeBiometric::STATUS_ACTIVE,
                            ]));

                            $created++;

                            return;
                        }

                        /*
                         * Only update source/sync fields.
                         * Do NOT overwrite manually edited fields:
                         * - biometric_company_id
                         * - display_employee_no
                         * - display_name
                         * - employment_status
                         * - remarks
                         */
                        $employeeBiometric->update($payload);

                        $updated++;
                    });
                }
            });

        $merged += $this->deduplicateExistingRecords();

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'merged' => $merged,
        ];
    }

    private function employeeIdentitySql(): string
    {
        return "
            CASE
                WHEN NULLIF(TRIM(CAST(employee_id AS CHAR)), '') IS NOT NULL
                    THEN CONCAT('employee_id:', LOWER(TRIM(CAST(employee_id AS CHAR))))

                WHEN NULLIF(TRIM(CAST(employee_no AS CHAR)), '') IS NOT NULL
                    THEN CONCAT('employee_no:', LOWER(TRIM(CAST(employee_no AS CHAR))))

                WHEN NULLIF(TRIM(employee_name), '') IS NOT NULL
                    THEN CONCAT('employee_name:', LOWER(TRIM(employee_name)))

                WHEN NULLIF(TRIM(CAST(crosschex_id AS CHAR)), '') IS NOT NULL
                    THEN CONCAT('crosschex_id:', LOWER(TRIM(CAST(crosschex_id AS CHAR))))

                ELSE NULL
            END
        ";
    }

    private function resolveExistingEmployeeBiometric(
        string $sourceKey,
        array $payload,
        int &$merged
    ): ?EmployeeBiometric {
        $employeeBiometric = EmployeeBiometric::query()
            ->where('source_key', $sourceKey)
            ->first();

        if ($employeeBiometric) {
            return $employeeBiometric;
        }

        $hasCondition = false;
        $normalizedName = $this->normalizeName($payload['source_employee_name'] ?? null);

        $candidatesQuery = EmployeeBiometric::query()
            ->where(function ($query) use ($payload, $normalizedName, &$hasCondition): void {
                $this->addCondition(
                    $query,
                    $hasCondition,
                    'source_employee_id',
                    $payload['source_employee_id'] ?? null
                );

                $this->addCondition(
                    $query,
                    $hasCondition,
                    'source_employee_no',
                    $payload['source_employee_no'] ?? null
                );

                $this->addCondition(
                    $query,
                    $hasCondition,
                    'display_employee_no',
                    $payload['source_employee_no'] ?? null
                );

                if ($this->isMergeableName($normalizedName)) {
                    $this->addRawCondition(
                        $query,
                        $hasCondition,
                        'LOWER(TRIM(source_employee_name)) = ?',
                        [$normalizedName]
                    );

                    $this->addRawCondition(
                        $query,
                        $hasCondition,
                        'LOWER(TRIM(display_name)) = ?',
                        [$normalizedName]
                    );
                }
            });

        if (! $hasCondition) {
            return null;
        }

        $candidates = $candidatesQuery->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        $keeper = $this->chooseKeeper($candidates);

        foreach ($candidates as $candidate) {
            if ((int) $candidate->getKey() === (int) $keeper->getKey()) {
                continue;
            }

            $this->mergeDuplicateIntoKeeper($keeper, $candidate);
            $merged++;
        }

        return $keeper->refresh();
    }

    private function deduplicateExistingRecords(): int
    {
        $merged = 0;

        $merged += $this->deduplicateByKey(function (EmployeeBiometric $record): ?string {
            $employeeId = $this->nullableString($record->source_employee_id);

            return $employeeId ? 'employee_id:'.$this->normalizeScalar($employeeId) : null;
        });

        $merged += $this->deduplicateByKey(function (EmployeeBiometric $record): ?string {
            $employeeNo = $this->nullableString($record->source_employee_no)
                ?: $this->nullableString($record->display_employee_no);

            return $employeeNo ? 'employee_no:'.$this->normalizeScalar($employeeNo) : null;
        });

        $merged += $this->deduplicateByKey(function (EmployeeBiometric $record): ?string {
            $name = $this->normalizeName($record->source_employee_name)
                ?: $this->normalizeName($record->display_name);

            return $this->isMergeableName($name) ? 'employee_name:'.$name : null;
        });

        return $merged;
    }

    private function deduplicateByKey(callable $resolver): int
    {
        $merged = 0;
        $records = EmployeeBiometric::query()
            ->orderBy('id')
            ->get();

        $groups = [];

        foreach ($records as $record) {
            $key = $resolver($record);

            if ($key === null) {
                continue;
            }

            $groups[$key][] = $record;
        }

        foreach ($groups as $groupRecords) {
            if (count($groupRecords) <= 1) {
                continue;
            }

            DB::transaction(function () use ($groupRecords, &$merged): void {
                $collection = collect($groupRecords);
                $keeper = $this->chooseKeeper($collection);

                foreach ($collection as $duplicate) {
                    if ((int) $duplicate->getKey() === (int) $keeper->getKey()) {
                        continue;
                    }

                    $this->mergeDuplicateIntoKeeper($keeper, $duplicate);
                    $merged++;
                }
            });
        }

        return $merged;
    }

    private function chooseKeeper(Collection $records): EmployeeBiometric
    {
        return $records
            ->sort(function (EmployeeBiometric $a, EmployeeBiometric $b): int {
                $scoreA = $this->manualScore($a);
                $scoreB = $this->manualScore($b);

                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA;
                }

                $latestA = $this->lastCheckTimestamp($a);
                $latestB = $this->lastCheckTimestamp($b);

                if ($latestA !== $latestB) {
                    return $latestB <=> $latestA;
                }

                $logsA = (int) $a->total_logs;
                $logsB = (int) $b->total_logs;

                if ($logsA !== $logsB) {
                    return $logsB <=> $logsA;
                }

                return (int) $a->getKey() <=> (int) $b->getKey();
            })
            ->first();
    }

    private function manualScore(EmployeeBiometric $record): int
    {
        $score = 0;

        if ($record->biometric_company_id !== null) {
            $score += 100;
        }

        if ($this->nullableString($record->display_employee_no) !== null) {
            $score += 50;
        }

        if ($this->isMergeableName($this->normalizeName($record->display_name))) {
            $score += 40;
        }

        if ($this->nullableString($record->remarks) !== null) {
            $score += 30;
        }

        if (
            $record->employment_status
            && $record->employment_status !== EmployeeBiometric::STATUS_ACTIVE
        ) {
            $score += 20;
        }

        return $score;
    }

    private function mergeDuplicateIntoKeeper(
        EmployeeBiometric $keeper,
        EmployeeBiometric $duplicate
    ): void {
        $updates = [];

        if ($keeper->biometric_company_id === null && $duplicate->biometric_company_id !== null) {
            $updates['biometric_company_id'] = $duplicate->biometric_company_id;
        }

        if (
            $this->nullableString($keeper->display_employee_no) === null
            && $this->nullableString($duplicate->display_employee_no) !== null
        ) {
            $updates['display_employee_no'] = $duplicate->display_employee_no;
        }

        if (
            ! $this->isMergeableName($this->normalizeName($keeper->display_name))
            && $this->isMergeableName($this->normalizeName($duplicate->display_name))
        ) {
            $updates['display_name'] = $duplicate->display_name;
        }

        if (
            $keeper->employment_status === EmployeeBiometric::STATUS_ACTIVE
            && $duplicate->employment_status
            && $duplicate->employment_status !== EmployeeBiometric::STATUS_ACTIVE
        ) {
            $updates['employment_status'] = $duplicate->employment_status;
        }

        if (
            $this->nullableString($keeper->remarks) === null
            && $this->nullableString($duplicate->remarks) !== null
        ) {
            $updates['remarks'] = $duplicate->remarks;
        }

        foreach ($this->sourceFields() as $field) {
            if (
                $this->nullableString($keeper->{$field}) === null
                && $this->nullableString($duplicate->{$field}) !== null
            ) {
                $updates[$field] = $duplicate->{$field};
            }
        }

        if ($this->lastCheckTimestamp($duplicate) > $this->lastCheckTimestamp($keeper)) {
            $updates['last_check_time'] = $duplicate->last_check_time;
        }

        if ((int) $duplicate->total_logs > (int) $keeper->total_logs) {
            $updates['total_logs'] = (int) $duplicate->total_logs;
        }

        if ($updates !== []) {
            $keeper->forceFill($updates)->save();
        }

        $duplicate->delete();
    }

    /**
     * @return array<int, string>
     */
    private function sourceFields(): array
    {
        return [
            'source_crosschex_account',
            'source_crosschex_account_name',
            'source_crosschex_id',
            'source_employee_id',
            'source_employee_no',
            'source_employee_name',
            'device_sn',
            'device_name',
        ];
    }

    private function addCondition(
        mixed $query,
        bool &$hasCondition,
        string $column,
        ?string $value
    ): void {
        $value = $this->nullableString($value);

        if ($value === null) {
            return;
        }

        if ($hasCondition) {
            $query->orWhere($column, $value);

            return;
        }

        $query->where($column, $value);
        $hasCondition = true;
    }

    /**
     * @param  array<int, mixed>  $bindings
     */
    private function addRawCondition(
        mixed $query,
        bool &$hasCondition,
        string $sql,
        array $bindings
    ): void {
        if ($hasCondition) {
            $query->orWhereRaw($sql, $bindings);

            return;
        }

        $query->whereRaw($sql, $bindings);
        $hasCondition = true;
    }

    private function makeSourceKey(string $employeeIdentityKey): string
    {
        return sha1('mirasol|'.Str::lower(trim($employeeIdentityKey)));
    }

    private function normalizeScalar(?string $value): ?string
    {
        $value = $this->nullableString($value);

        return $value === null ? null : Str::lower($value);
    }

    private function normalizeName(?string $value): ?string
    {
        $value = $this->nullableString($value);

        if ($value === null) {
            return null;
        }

        $value = preg_replace('/\s+/', ' ', $value);

        return Str::lower(trim((string) $value));
    }

    private function isMergeableName(?string $value): bool
    {
        $value = $this->nullableString($value);

        if ($value === null) {
            return false;
        }

        return ! in_array($value, [
            'unknown',
            'unknown biometric employee',
            'n/a',
            'na',
            '-',
        ], true);
    }

    private function lastCheckTimestamp(EmployeeBiometric $record): int
    {
        if (! $record->last_check_time) {
            return 0;
        }

        return Carbon::parse($record->last_check_time)->getTimestamp();
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
