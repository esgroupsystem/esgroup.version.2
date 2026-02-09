<?php

namespace App\Console\Commands;

use App\Models\MirasolBiometricsLog;
use App\Services\CrossChexService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrossChexSyncAttendance extends Command
{
    // ✅ Add --from and --to for manual sync
    protected $signature = 'crosschex:sync {--from=} {--to=} {--debug=0}';

    protected $description = 'Sync attendance logs from CrossChex Cloud to mirasol_biometrics_logs';

    public function handle(CrossChexService $api): int
    {
        $fromOpt = (string) $this->option('from');
        $toOpt   = (string) $this->option('to');
        $isManualRange = $fromOpt !== '' && $toOpt !== '';

        // ✅ If manual range provided, use it; otherwise use last_sync → now
        if ($isManualRange) {
            $from = Carbon::parse($fromOpt)->toDateTimeString();
            $to   = Carbon::parse($toOpt)->toDateTimeString();
        } else {
            $from = (string) DB::table('settings')->where('key', 'crosschex_last_sync')->value('value');
            if (! $from) {
                $from = now()->subDay()->toDateTimeString();
            }
            $to = now()->toDateTimeString();
        }

        $page = 1;
        $perPage = 1000;
        $saved = 0;

        while (true) {
            $json = $api->getAttendanceRecords($from, $to, $page, $perPage);

            if (data_get($json, 'header.name') === 'Exception') {
                $type = (string) data_get($json, 'payload.type');
                $msg  = (string) data_get($json, 'payload.message');

                if ($type === 'FREQUENT_REQUEST') {
                    $this->warn("CrossChex rate limit: {$msg}. Sleeping 31 seconds then retry page {$page}...");
                    sleep(31);
                    continue;
                }

                $this->error("CrossChex Error: {$type} - {$msg}");
                if ((int) $this->option('debug') === 1) {
                    $this->line(json_encode($json, JSON_PRETTY_PRINT));
                }

                return 1;
            }

            if ((int) $this->option('debug') === 1) {
                $this->line("PAGE: {$page}");
                $this->line(json_encode($json, JSON_PRETTY_PRINT));
            }

            $list = data_get($json, 'payload.list')
                ?? data_get($json, 'payload.data.list')
                ?? data_get($json, 'payload.records')
                ?? [];

            if (! is_array($list) || empty($list)) {
                break;
            }

            foreach ($list as $r) {
                $crossId = data_get($r, 'uuid') ?? data_get($r, 'id');
                if (! $crossId) continue;

                $employeeNo = data_get($r, 'employee.workno')
                    ?? data_get($r, 'workno')
                    ?? data_get($r, 'employee_no');

                $employeeName = data_get($r, 'employee_name')
                    ?? trim((data_get($r, 'employee.first_name') ?? '') . ' ' . (data_get($r, 'employee.last_name') ?? ''));

                $checkTimeRaw = data_get($r, 'checktime')
                    ?? data_get($r, 'check_time')
                    ?? data_get($r, 'time');

                $deviceSn = data_get($r, 'device.serial_number')
                    ?? data_get($r, 'device_sn')
                    ?? data_get($r, 'sn');

                $deviceName = data_get($r, 'device.name') ?? null;
                $state      = data_get($r, 'state') ?? data_get($r, 'type') ?? null;

                if (! $employeeNo || ! $checkTimeRaw) continue;

                // ✅ Treat API time as UTC then convert to Manila
                $checkTime = Carbon::parse($checkTimeRaw, 'UTC')
                    ->setTimezone('Asia/Manila')
                    ->format('Y-m-d H:i:s');

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

            $pageCount   = (int) data_get($json, 'payload.pageCount', 1);
            $currentPage = (int) data_get($json, 'payload.page', $page);

            if ($currentPage >= $pageCount) break;
            $page++;
        }

        // ✅ Only advance last_sync for automatic runs (not manual range)
        if (! $isManualRange) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'crosschex_last_sync'],
                ['value' => $to, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $mode = $isManualRange ? 'MANUAL' : 'AUTO';
        $this->info("[{$mode}] Done! Synced from {$from} to {$to}. Saved/Updated: {$saved}");

        return 0;
    }
}
