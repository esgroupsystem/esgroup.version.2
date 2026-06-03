<?php

namespace App\Jobs;

use App\Services\CrossChexService;
use App\Services\CrossChexServiceFactory;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrossChexSyncLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $jobId;

    public string $from;

    public string $to;

    public array $accounts;

    public int $timeout = 1200;

    public int $tries = 10;

    public int $maxExceptions = 3;

    public function __construct(string $jobId, string $from, string $to, array $accounts = [])
    {
        $this->jobId = $jobId;
        $this->from = $from;
        $this->to = $to;
        $this->accounts = $accounts;
    }

    public function backoff(): array
    {
        return [60, 120, 300, 600];
    }

    private function statusKey(): string
    {
        return "crosschex_sync_status:{$this->jobId}";
    }

    private function setStatus(array $data): void
    {
        Cache::put($this->statusKey(), $data, now()->addMinutes(60));
    }

    private function clearActiveJob(): void
    {
        $activeJobId = Cache::get('crosschex_active_job_id');

        if ($activeJobId === $this->jobId) {
            Cache::forget('crosschex_active_job_id');
        }
    }

    private function normalizeCheckTime(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $timezone = config('app.timezone', 'Asia/Manila');

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

        try {
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

    public function handle(CrossChexServiceFactory $factory): void
    {
        $accounts = ! empty($this->accounts)
            ? $this->accounts
            : $factory->accounts();

        if (empty($accounts)) {
            throw new \RuntimeException('No CrossChex accounts configured.');
        }

        $totalInserted = 0;
        $totalUpdated = 0;
        $accountCount = count($accounts);

        foreach ($accounts as $index => $account) {
            $api = $factory->make($account);

            [$inserted, $updated] = $this->syncSingleAccount(
                api: $api,
                accountNumber: $index + 1,
                accountCount: $accountCount
            );

            $totalInserted += $inserted;
            $totalUpdated += $updated;
        }

        $this->setStatus([
            'state' => 'done',
            'message' => "All CrossChex accounts synced. Inserted: {$totalInserted}, Updated: {$totalUpdated}",
            'from' => $this->from,
            'to' => $this->to,
            'page' => null,
            'pageCount' => null,
            'saved' => $totalInserted,
            'updated' => $totalUpdated,
            'percent' => 100,
            'done' => true,
            'error' => null,
        ]);

        $this->clearActiveJob();
    }

    private function syncSingleAccount(
        CrossChexService $api,
        int $accountNumber,
        int $accountCount
    ): array {
        $page = 1;
        $perPage = 200;
        $saved = 0;
        $updated = 0;
        $pageCount = 1;
        $rateLimitHits = 0;

        while (true) {
            $json = $api->getAttendanceRecords($this->from, $this->to, $page, $perPage);

            if (data_get($json, 'header.name') === 'Exception') {
                $type = (string) data_get($json, 'payload.type');
                $message = (string) data_get($json, 'payload.message');

                if (in_array($type, ['FREQUENT_REQUEST', 'TOO_MANY_REQUESTS'], true)) {
                    $rateLimitHits++;
                    $waitSeconds = min(30 * $rateLimitHits, 120);

                    $this->setStatus([
                        'state' => 'running',
                        'message' => "{$api->accountName()} rate limited. Waiting {$waitSeconds} seconds...",
                        'from' => $this->from,
                        'to' => $this->to,
                        'account' => $api->account(),
                        'accountName' => $api->accountName(),
                        'page' => $page,
                        'pageCount' => $pageCount,
                        'saved' => $saved,
                        'updated' => $updated,
                        'percent' => 0,
                        'done' => false,
                        'error' => null,
                    ]);

                    sleep($waitSeconds);

                    continue;
                }

                if (in_array($type, ['TOKEN_EXPIRED', 'INVALID_TOKEN', 'UNAUTHORIZED'], true)) {
                    $api->clearToken();
                    sleep(2);

                    continue;
                }

                throw new \RuntimeException("{$api->accountName()} CrossChex error: {$type} - {$message}");
            }

            $rateLimitHits = 0;

            $list = data_get($json, 'payload.list', []);
            $pageCount = (int) data_get($json, 'payload.pageCount', 1);
            $currentPage = (int) data_get($json, 'payload.page', $page);

            if (! is_array($list) || empty($list)) {
                break;
            }

            $rows = [];
            $crossIds = [];
            $now = now();

            foreach ($list as $record) {
                $crossId = data_get($record, 'uuid')
                    ?? data_get($record, 'id')
                    ?? data_get($record, 'record_id');

                if (! $crossId) {
                    continue;
                }

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

                $deviceSn = data_get($record, 'device.serial_number')
                    ?? data_get($record, 'device.sn')
                    ?? data_get($record, 'device_sn')
                    ?? data_get($record, 'sn');

                $deviceName = data_get($record, 'device.name')
                    ?? data_get($record, 'device_name');

                $state = data_get($record, 'state')
                    ?? data_get($record, 'type')
                    ?? data_get($record, 'check_type');

                if (! $employeeNo || ! $checkTime) {
                    continue;
                }

                $crossId = (string) $crossId;
                $crossIds[] = $crossId;

                $rows[] = [
                    'crosschex_account' => $api->account(),
                    'crosschex_account_name' => $api->accountName(),
                    'crosschex_id' => $crossId,
                    'employee_id' => null,
                    'employee_no' => (string) $employeeNo,
                    'employee_name' => $employeeName ?: null,
                    'check_time' => $checkTime,
                    'device_sn' => $deviceSn ? (string) $deviceSn : null,
                    'device_name' => $deviceName,
                    'state' => $state,
                    'raw' => json_encode($record, JSON_UNESCAPED_UNICODE),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($rows)) {
                $existingIds = DB::table('mirasol_biometrics_logs')
                    ->where('crosschex_account', $api->account())
                    ->whereIn('crosschex_id', $crossIds)
                    ->pluck('crosschex_id')
                    ->map(fn ($id) => (string) $id)
                    ->all();

                $existingMap = array_flip($existingIds);

                foreach ($crossIds as $id) {
                    if (isset($existingMap[$id])) {
                        $updated++;
                    } else {
                        $saved++;
                    }
                }

                DB::table('mirasol_biometrics_logs')->upsert(
                    $rows,
                    ['crosschex_account', 'crosschex_id'],
                    [
                        'crosschex_account_name',
                        'employee_id',
                        'employee_no',
                        'employee_name',
                        'check_time',
                        'device_sn',
                        'device_name',
                        'state',
                        'raw',
                        'updated_at',
                    ]
                );
            }

            $accountBaseProgress = (($accountNumber - 1) / max($accountCount, 1)) * 100;

            $pageProgress = $pageCount > 0
                ? ($currentPage / $pageCount) * (100 / max($accountCount, 1))
                : 0;

            $percent = (int) min(99, round($accountBaseProgress + $pageProgress));

            $this->setStatus([
                'state' => 'running',
                'message' => "Syncing {$api->accountName()} page {$currentPage} of {$pageCount}...",
                'from' => $this->from,
                'to' => $this->to,
                'account' => $api->account(),
                'accountName' => $api->accountName(),
                'page' => $currentPage,
                'pageCount' => $pageCount,
                'saved' => $saved,
                'updated' => $updated,
                'percent' => $percent,
                'done' => false,
                'error' => null,
            ]);

            if ($currentPage >= $pageCount) {
                break;
            }

            $page++;
            sleep(2);
        }

        return [$saved, $updated];
    }

    public function failed(\Throwable $e): void
    {
        Log::error('CrossChexSyncLogsJob permanently failed', [
            'jobId' => $this->jobId,
            'from' => $this->from,
            'to' => $this->to,
            'error' => $e->getMessage(),
        ]);

        $this->setStatus([
            'state' => 'failed',
            'message' => 'Job permanently failed: '.$e->getMessage(),
            'from' => $this->from,
            'to' => $this->to,
            'page' => 0,
            'pageCount' => null,
            'saved' => 0,
            'updated' => 0,
            'percent' => 0,
            'done' => true,
            'error' => $e->getMessage(),
        ]);

        $this->clearActiveJob();
    }
}
