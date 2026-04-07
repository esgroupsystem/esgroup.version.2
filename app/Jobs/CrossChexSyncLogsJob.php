<?php

namespace App\Jobs;

use App\Services\CrossChexService;
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

    public int $timeout = 1200;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(string $jobId, string $from, string $to)
    {
        $this->jobId = $jobId;
        $this->from = $from;
        $this->to = $to;
    }

    private function statusKey(): string
    {
        return "crosschex_sync_status:{$this->jobId}";
    }

    private function setStatus(array $data): void
    {
        Cache::put($this->statusKey(), $data, now()->addMinutes(60));
    }

    private function normalizeCheckTime(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $tz = config('app.timezone', 'Asia/Manila');

        if (is_numeric($raw)) {
            $num = (int) $raw;

            if ($num > 9999999999) {
                return Carbon::createFromTimestampMs($num, 'UTC')
                    ->setTimezone($tz)
                    ->format('Y-m-d H:i:s');
            }

            return Carbon::createFromTimestamp($num, 'UTC')
                ->setTimezone($tz)
                ->format('Y-m-d H:i:s');
        }

        $str = trim((string) $raw);

        try {
            if (preg_match('/[zZ]|[+\-]\d{2}:\d{2}$/', $str)) {
                return Carbon::parse($str)
                    ->setTimezone($tz)
                    ->format('Y-m-d H:i:s');
            }

            if (str_contains($str, 'T')) {
                return Carbon::parse($str, 'UTC')
                    ->setTimezone($tz)
                    ->format('Y-m-d H:i:s');
            }

            return Carbon::parse($str, 'UTC')
                ->setTimezone($tz)
                ->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    public function handle(CrossChexService $api): void
    {
        $page = 1;
        $perPage = 500; // safer than 1000 if server is small
        $saved = 0;
        $updated = 0;
        $startedAt = microtime(true);

        $this->setStatus([
            'state' => 'running',
            'message' => 'Starting sync...',
            'from' => $this->from,
            'to' => $this->to,
            'page' => 0,
            'pageCount' => null,
            'saved' => 0,
            'updated' => 0,
            'percent' => 0,
            'done' => false,
            'error' => null,
        ]);

        try {
            $pageCount = 1;

            while (true) {
                $json = $api->getAttendanceRecords($this->from, $this->to, $page, $perPage);

                if (data_get($json, 'header.name') === 'Exception') {
                    $type = (string) data_get($json, 'payload.type');
                    $msg = (string) data_get($json, 'payload.message');

                    if ($type === 'FREQUENT_REQUEST') {
                        Log::warning('CrossChex rate limit hit', [
                            'jobId' => $this->jobId,
                            'page' => $page,
                            'from' => $this->from,
                            'to' => $this->to,
                        ]);

                        $this->release($this->backoff);

                        return;
                    }

                    $this->setStatus([
                        'state' => 'failed',
                        'message' => "CrossChex Error: {$type} - {$msg}",
                        'from' => $this->from,
                        'to' => $this->to,
                        'page' => $page,
                        'pageCount' => null,
                        'saved' => $saved,
                        'updated' => $updated,
                        'percent' => 0,
                        'done' => true,
                        'error' => "{$type} - {$msg}",
                    ]);

                    Log::error('CrossChex API error', [
                        'jobId' => $this->jobId,
                        'type' => $type,
                        'message' => $msg,
                        'page' => $page,
                    ]);

                    return;
                }

                $list = data_get($json, 'payload.list', []);
                $pageCount = (int) data_get($json, 'payload.pageCount', 1);
                $currentPage = (int) data_get($json, 'payload.page', $page);
                $percent = $pageCount > 0 ? (int) round(($currentPage / $pageCount) * 100) : 0;

                if (! is_array($list) || empty($list)) {
                    break;
                }

                $rows = [];
                $crossIds = [];
                $now = now();

                foreach ($list as $r) {
                    $crossId = data_get($r, 'uuid') ?? data_get($r, 'id');
                    if (! $crossId) {
                        continue;
                    }

                    $employeeNo = data_get($r, 'employee.workno')
                        ?? data_get($r, 'workno')
                        ?? data_get($r, 'employee_no');

                    $employeeName = data_get($r, 'employee_name')
                        ?? trim(
                            (data_get($r, 'employee.first_name') ?? '').' '.
                            (data_get($r, 'employee.last_name') ?? '')
                        );

                    $checkTimeRaw = data_get($r, 'checktime')
                        ?? data_get($r, 'check_time')
                        ?? data_get($r, 'time');

                    $checkTime = $this->normalizeCheckTime($checkTimeRaw);

                    $deviceSn = data_get($r, 'device.serial_number')
                        ?? data_get($r, 'device_sn')
                        ?? data_get($r, 'sn');

                    $deviceName = data_get($r, 'device.name') ?? null;
                    $state = data_get($r, 'state') ?? data_get($r, 'type') ?? null;

                    if (! $employeeNo || ! $checkTime) {
                        continue;
                    }

                    $crossId = (string) $crossId;
                    $crossIds[] = $crossId;

                    $rows[] = [
                        'crosschex_id' => $crossId,
                        'employee_id' => null,
                        'employee_no' => (string) $employeeNo,
                        'employee_name' => $employeeName ?: null,
                        'device_sn' => $deviceSn ? (string) $deviceSn : null,
                        'device_name' => $deviceName,
                        'state' => $state,
                        'check_time' => $checkTime,
                        'raw' => json_encode($r, JSON_UNESCAPED_UNICODE),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (! empty($rows)) {
                    $existingIds = DB::table('mirasol_biometrics_logs')
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
                        ['crosschex_id'],
                        [
                            'employee_no',
                            'employee_name',
                            'device_sn',
                            'device_name',
                            'state',
                            'check_time',
                            'raw',
                            'updated_at',
                        ]
                    );
                }

                $this->setStatus([
                    'state' => 'running',
                    'message' => "Syncing page {$currentPage} of {$pageCount}...",
                    'from' => $this->from,
                    'to' => $this->to,
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
            }

            $duration = round(microtime(true) - $startedAt, 2);

            $this->setStatus([
                'state' => 'done',
                'message' => "Sync finished. Inserted: {$saved}, Updated: {$updated}",
                'from' => $this->from,
                'to' => $this->to,
                'page' => $page,
                'pageCount' => $pageCount ?? null,
                'saved' => $saved,
                'updated' => $updated,
                'percent' => 100,
                'done' => true,
                'error' => null,
            ]);

            Log::info('CrossChexSyncLogsJob finished', [
                'jobId' => $this->jobId,
                'from' => $this->from,
                'to' => $this->to,
                'inserted' => $saved,
                'updated' => $updated,
                'duration_seconds' => $duration,
            ]);
        } catch (\Throwable $e) {
            Log::error('CrossChexSyncLogsJob failed', [
                'jobId' => $this->jobId,
                'from' => $this->from,
                'to' => $this->to,
                'page' => $page,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->setStatus([
                'state' => 'failed',
                'message' => 'Sync failed. Check logs.',
                'from' => $this->from,
                'to' => $this->to,
                'page' => $page,
                'pageCount' => null,
                'saved' => $saved,
                'updated' => $updated,
                'percent' => 0,
                'done' => true,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
