<?php

namespace App\Jobs;

use App\Services\Biometrics\CrossChexAttendanceSyncService;
use App\Services\CrossChexServiceFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

/**
 * Optional legacy/background synchronization job.
 *
 * The Biometrics Sync web page no longer dispatches this job and therefore
 * does not require `php artisan queue:work`. This class is retained only for
 * code that may intentionally choose queued/background processing later.
 */
class CrossChexSyncLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;

    public int $tries = 5;

    public function __construct(
        public string $jobId,
        public string $from,
        public string $to,
        public array $accounts = [],
    ) {
    }

    public function backoff(): array
    {
        return [30, 60, 120, 300];
    }

    public function handle(
        CrossChexServiceFactory $factory,
        CrossChexAttendanceSyncService $syncService,
    ): void {
        $accounts = $this->accounts !== [] ? $this->accounts : $factory->accounts();

        if ($accounts === []) {
            throw new \RuntimeException('No CrossChex accounts configured.');
        }

        $inserted = 0;
        $skipped = 0;
        $invalid = 0;
        $accountCount = count($accounts);
        $perPage = (int) config('services.crosschex.sync.per_page', 200);

        foreach ($accounts as $accountIndex => $account) {
            $api = $factory->make($account);
            $page = 1;

            while (true) {
                $result = $syncService->syncPage(
                    api: $api,
                    from: $this->from,
                    to: $this->to,
                    page: $page,
                    perPage: $perPage,
                );

                if ($result['rate_limited']) {
                    sleep(max(1, (int) $result['retry_after']));
                    continue;
                }

                $inserted += (int) $result['inserted'];
                $skipped += (int) $result['skipped'];
                $invalid += (int) $result['invalid'];

                $withinAccount = min(1, ((int) $result['page']) / max(1, (int) $result['page_count']));
                $percent = min(
                    99,
                    (int) floor((($accountIndex + $withinAccount) / max(1, $accountCount)) * 100)
                );

                $this->setStatus([
                    'state' => 'running',
                    'message' => "Syncing {$api->accountName()} page {$result['page']} of {$result['page_count']}...",
                    'from' => $this->from,
                    'to' => $this->to,
                    'account' => $api->account(),
                    'accountName' => $api->accountName(),
                    'page' => $result['page'],
                    'pageCount' => $result['page_count'],
                    'saved' => $inserted,
                    'skipped' => $skipped,
                    'invalid' => $invalid,
                    'percent' => $percent,
                    'done' => false,
                    'error' => null,
                ]);

                if ((int) $result['page'] >= (int) $result['page_count'] || (int) $result['fetched'] === 0) {
                    break;
                }

                $page = (int) $result['page'] + 1;
            }
        }

        $this->setStatus([
            'state' => 'done',
            'message' => "Sync complete. New: {$inserted}, already saved: {$skipped}, invalid: {$invalid}.",
            'from' => $this->from,
            'to' => $this->to,
            'account' => null,
            'accountName' => null,
            'page' => null,
            'pageCount' => null,
            'saved' => $inserted,
            'skipped' => $skipped,
            'invalid' => $invalid,
            'percent' => 100,
            'done' => true,
            'error' => null,
        ]);
    }

    private function setStatus(array $data): void
    {
        Cache::put(
            "crosschex_sync_status:{$this->jobId}",
            $data,
            now()->addMinutes((int) config('services.crosschex.sync.session_minutes', 120))
        );
    }
}
