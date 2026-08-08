<?php

namespace App\Services\Biometrics;

use App\Services\CrossChexService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CrossChexAttendanceSyncService
{
    /**
     * Synchronize one CrossChex API page.
     *
     * The web UI intentionally calls this one page at a time. This keeps each
     * HTTP request short, removes the queue-worker requirement, and allows the
     * browser to show exact progress while the synchronization is running.
     */
    public function syncPage(
        CrossChexService $api,
        string $from,
        string $to,
        int $page,
        int $perPage
    ): array {
        $json = $this->fetchPage($api, $from, $to, $page, $perPage);

        if ($this->isRateLimited($json)) {
            return [
                'rate_limited' => true,
                'retry_after' => (int) config('services.crosschex.sync.rate_limit_retry_seconds', 31),
                'message' => (string) data_get($json, 'payload.message', 'CrossChex rate limit reached.'),
                'page' => $page,
                'page_count' => max(1, (int) data_get($json, 'payload.pageCount', 1)),
                'fetched' => 0,
                'inserted' => 0,
                'skipped' => 0,
                'invalid' => 0,
            ];
        }

        $this->throwIfApiException($api, $json);

        $list = data_get($json, 'payload.list')
            ?? data_get($json, 'payload.data.list')
            ?? data_get($json, 'payload.records')
            ?? [];

        if (! is_array($list)) {
            $list = [];
        }

        $pageCount = max(
            1,
            (int) (
                data_get($json, 'payload.pageCount')
                ?? data_get($json, 'payload.data.pageCount')
                ?? 1
            )
        );

        $currentPage = max(
            1,
            (int) (
                data_get($json, 'payload.page')
                ?? data_get($json, 'payload.data.page')
                ?? $page
            )
        );

        $rowsByCrossChexId = [];
        $invalid = 0;
        $now = now();

        foreach ($list as $record) {
            if (! is_array($record)) {
                $invalid++;
                continue;
            }

            $row = $this->mapRecord($api, $record, $now);

            if ($row === null) {
                $invalid++;
                continue;
            }

            // Deduplicate records returned more than once in the same API page.
            // The database unique index is still the final source of truth.
            $rowsByCrossChexId[$row['crosschex_id']] = $row;
        }

        $rows = array_values($rowsByCrossChexId);
        $candidateCount = count($rows);
        $inserted = 0;

        if ($candidateCount > 0) {
            /*
             * Fast path:
             * - no SELECT query to check every existing CrossChex ID;
             * - no UPDATE of attendance rows that already exist;
             * - MySQL unique indexes reject duplicates at database level;
             * - insertOrIgnore reports only newly inserted records.
             *
             * This makes repeated synchronization of the same date range much
             * faster than updateOrCreate/upsert when attendance logs are treated
             * as immutable source records.
             */
            $inserted = (int) DB::table('mirasol_biometrics_logs')->insertOrIgnore($rows);
        }

        return [
            'rate_limited' => false,
            'retry_after' => 0,
            'message' => null,
            'page' => $currentPage,
            'page_count' => $pageCount,
            'fetched' => count($list),
            'inserted' => $inserted,
            'skipped' => max(0, $candidateCount - $inserted),
            'invalid' => $invalid,
        ];
    }

    private function fetchPage(
        CrossChexService $api,
        string $from,
        string $to,
        int $page,
        int $perPage
    ): array {
        $json = $api->getAttendanceRecords($from, $to, $page, $perPage);

        if ($this->isTokenException($json)) {
            $api->clearToken();
            $json = $api->getAttendanceRecords($from, $to, $page, $perPage);
        }

        return $json;
    }

    private function mapRecord(CrossChexService $api, array $record, $now): ?array
    {
        $crossId = data_get($record, 'uuid')
            ?? data_get($record, 'id')
            ?? data_get($record, 'record_id');

        if (! filled($crossId)) {
            return null;
        }

        $employeeId = data_get($record, 'employee.uuid')
            ?? data_get($record, 'employee.id')
            ?? data_get($record, 'employee.employee_id')
            ?? data_get($record, 'employee_id')
            ?? data_get($record, 'person.uuid')
            ?? data_get($record, 'person.id');

        $employeeNo = data_get($record, 'employee.workno')
            ?? data_get($record, 'employee.employee_no')
            ?? data_get($record, 'workno')
            ?? data_get($record, 'employee_no');

        $employeeName = data_get($record, 'employee_name')
            ?? data_get($record, 'employee.name')
            ?? trim(
                (string) data_get($record, 'employee.first_name', '').' '.
                (string) data_get($record, 'employee.last_name', '')
            );

        $checkTimeRaw = data_get($record, 'checktime')
            ?? data_get($record, 'check_time')
            ?? data_get($record, 'time');

        $checkTime = $this->normalizeCheckTime($checkTimeRaw);

        if (! filled($employeeNo) || $checkTime === null) {
            return null;
        }

        $deviceSn = data_get($record, 'device.serial_number')
            ?? data_get($record, 'device.sn')
            ?? data_get($record, 'device_sn')
            ?? data_get($record, 'sn');

        $deviceName = data_get($record, 'device.name')
            ?? data_get($record, 'device_name');

        $state = data_get($record, 'state')
            ?? data_get($record, 'type')
            ?? data_get($record, 'check_type');

        $sourceEmployeeId = filled($employeeId)
            ? trim((string) $employeeId)
            : null;

        // Keep the legacy BIGINT column numeric-only. CrossChex employee UUIDs
        // are preserved without truncation in source_employee_id.
        $legacyEmployeeId = $sourceEmployeeId !== null && ctype_digit($sourceEmployeeId)
            ? $sourceEmployeeId
            : null;

        return [
            'crosschex_account' => $api->account(),
            'crosschex_account_name' => $api->accountName(),
            'crosschex_id' => trim((string) $crossId),
            'source_employee_id' => $sourceEmployeeId,
            'employee_id' => $legacyEmployeeId,
            'employee_no' => trim((string) $employeeNo),
            'employee_name' => filled($employeeName) ? trim((string) $employeeName) : null,
            'check_time' => $checkTime,
            'device_sn' => filled($deviceSn) ? trim((string) $deviceSn) : null,
            'device_name' => filled($deviceName) ? trim((string) $deviceName) : null,
            'state' => filled($state) ? trim((string) $state) : null,
            'raw' => json_encode(
                $record,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
            ),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function normalizeCheckTime(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $timezone = config('app.timezone', 'Asia/Manila');

        try {
            if (is_numeric($raw)) {
                $number = (int) $raw;

                if ($number > 9999999999) {
                    return Carbon::createFromTimestampMs($number, 'UTC')
                        ->setTimezone($timezone)
                        ->format('Y-m-d H:i:s');
                }

                return Carbon::createFromTimestamp($number, 'UTC')
                    ->setTimezone($timezone)
                    ->format('Y-m-d H:i:s');
            }

            $value = trim((string) $raw);

            if (preg_match('/[zZ]|[+\-]\d{2}:\d{2}$/', $value)) {
                return Carbon::parse($value)
                    ->setTimezone($timezone)
                    ->format('Y-m-d H:i:s');
            }

            return Carbon::parse($value, 'UTC')
                ->setTimezone($timezone)
                ->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private function isRateLimited(array $json): bool
    {
        if (data_get($json, 'header.name') !== 'Exception') {
            return false;
        }

        return in_array(
            (string) data_get($json, 'payload.type'),
            ['FREQUENT_REQUEST', 'TOO_MANY_REQUESTS'],
            true
        );
    }

    private function isTokenException(array $json): bool
    {
        if (data_get($json, 'header.name') !== 'Exception') {
            return false;
        }

        return in_array(
            (string) data_get($json, 'payload.type'),
            ['TOKEN_EXPIRED', 'INVALID_TOKEN', 'UNAUTHORIZED'],
            true
        );
    }

    private function throwIfApiException(CrossChexService $api, array $json): void
    {
        if (data_get($json, 'header.name') !== 'Exception') {
            return;
        }

        $type = (string) data_get($json, 'payload.type', 'UNKNOWN');
        $message = (string) data_get($json, 'payload.message', 'Unknown CrossChex error.');

        throw new \RuntimeException(
            "{$api->accountName()} CrossChex error: {$type} - {$message}"
        );
    }
}
