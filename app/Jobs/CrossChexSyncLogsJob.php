<?php

namespace App\Jobs;

use App\Models\MirasolBiometricsLog;
use App\Services\CrossChexService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CrossChexSyncLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $jobId;
    public string $from;
    public string $to;

    public int $timeout = 1200;

    public function __construct(string $jobId, string $from, string $to)
    {
        $this->jobId = $jobId;
        $this->from  = $from;
        $this->to    = $to;
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
    if ($raw === null || $raw === '') return null;

    $tz = config('app.timezone', 'Asia/Manila');

    // Numeric timestamp (seconds/ms) -> assume UTC then convert
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
        // If the string includes timezone info (Z or +hh:mm), Carbon will respect it.
        if (preg_match('/[zZ]|[+\-]\d{2}:\d{2}$/', $str)) {
            return Carbon::parse($str)->setTimezone($tz)->format('Y-m-d H:i:s');
        }

        // If ISO "T" format but no explicit timezone, still assume UTC (common API behavior)
        if (str_contains($str, 'T')) {
            return Carbon::parse($str, 'UTC')->setTimezone($tz)->format('Y-m-d H:i:s');
        }

        // Plain datetime without timezone -> assume UTC then convert to Manila
        return Carbon::parse($str, 'UTC')->setTimezone($tz)->format('Y-m-d H:i:s');
    } catch (\Throwable) {
        return null;
    }
}


    public function handle(CrossChexService $api): void
    {
        $page = 1;
        $perPage = 1000;
        $saved = 0;

        $this->setStatus([
            'state' => 'running',
            'message' => 'Starting sync...',
            'from' => $this->from,
            'to' => $this->to,
            'page' => 0,
            'pageCount' => null,
            'saved' => 0,
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
                    $msg  = (string) data_get($json, 'payload.message');

                    if ($type === 'FREQUENT_REQUEST') {
                        $this->setStatus([
                            'state' => 'running',
                            'message' => "Rate limit hit. Waiting 31s then retry page {$page}...",
                            'from' => $this->from,
                            'to' => $this->to,
                            'page' => $page,
                            'pageCount' => data_get($json, 'payload.pageCount'),
                            'saved' => $saved,
                            'percent' => null,
                            'done' => false,
                            'error' => null,
                        ]);
                        sleep(31);
                        continue;
                    }

                    $this->setStatus([
                        'state' => 'failed',
                        'message' => "CrossChex Error: {$type} - {$msg}",
                        'from' => $this->from,
                        'to' => $this->to,
                        'page' => $page,
                        'pageCount' => null,
                        'saved' => $saved,
                        'percent' => 0,
                        'done' => true,
                        'error' => "{$type} - {$msg}",
                    ]);
                    return;
                }

                $list = data_get($json, 'payload.list', []);
                $pageCount = (int) data_get($json, 'payload.pageCount', 1);
                $currentPage = (int) data_get($json, 'payload.page', $page);

                $percent = $pageCount > 0 ? (int) round(($currentPage / $pageCount) * 100) : 0;

                $this->setStatus([
                    'state' => 'running',
                    'message' => "Syncing page {$currentPage} of {$pageCount}...",
                    'from' => $this->from,
                    'to' => $this->to,
                    'page' => $currentPage,
                    'pageCount' => $pageCount,
                    'saved' => $saved,
                    'percent' => $percent,
                    'done' => false,
                    'error' => null,
                ]);

                if (!is_array($list) || empty($list)) break;

                foreach ($list as $r) {
                    $crossId = data_get($r, 'uuid') ?? data_get($r, 'id');
                    if (!$crossId) continue;

                    $employeeNo = data_get($r, 'employee.workno')
                        ?? data_get($r, 'workno')
                        ?? data_get($r, 'employee_no');

                    $employeeName = data_get($r, 'employee_name')
                        ?? trim((data_get($r, 'employee.first_name') ?? '') . ' ' . (data_get($r, 'employee.last_name') ?? ''));

                    $checkTimeRaw = data_get($r, 'checktime')
                        ?? data_get($r, 'check_time')
                        ?? data_get($r, 'time');

                    $checkTime = $this->normalizeCheckTime($checkTimeRaw);

                    $deviceSn = data_get($r, 'device.serial_number')
                        ?? data_get($r, 'device_sn')
                        ?? data_get($r, 'sn');

                    $deviceName = data_get($r, 'device.name') ?? null;
                    $state      = data_get($r, 'state') ?? data_get($r, 'type') ?? null;

                    if (!$employeeNo || !$checkTime) continue;

                    MirasolBiometricsLog::updateOrCreate(
                        ['crosschex_id' => (string) $crossId],
                        [
                            'employee_id'   => null,
                            'employee_no'   => (string) $employeeNo,
                            'employee_name' => $employeeName ?: null,
                            'device_sn'     => $deviceSn ? (string) $deviceSn : null,
                            'device_name'   => $deviceName,
                            'state'         => $state,
                            'check_time'    => $checkTime,
                            'raw'           => $r,
                        ]
                    );

                    $saved++;
                }

                if ($currentPage >= $pageCount) break;
                $page++;
            }

            $this->setStatus([
                'state' => 'done',
                'message' => "Sync finished. Saved/Updated: {$saved}",
                'from' => $this->from,
                'to' => $this->to,
                'page' => $page,
                'pageCount' => $pageCount ?? null,
                'saved' => $saved,
                'percent' => 100,
                'done' => true,
                'error' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('CrossChexSyncLogsJob failed', ['jobId' => $this->jobId, 'error' => $e->getMessage()]);
            $this->setStatus([
                'state' => 'failed',
                'message' => 'Sync failed. Check laravel.log.',
                'from' => $this->from,
                'to' => $this->to,
                'page' => $page,
                'pageCount' => null,
                'saved' => $saved,
                'percent' => 0,
                'done' => true,
                'error' => 'Sync failed. Check laravel.log.',
            ]);
        }
    }
}
