<?php

namespace App\Services\Biometrics;

use App\Services\CrossChexServiceFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CrossChexSyncCoordinator
{
    public function __construct(
        private readonly CrossChexServiceFactory $factory,
        private readonly CrossChexAttendanceSyncService $attendanceSyncService,
    ) {
    }

    public function start(Carbon $from, Carbon $to, array $requestedAccounts): array
    {
        $configured = $this->factory->configuredAccounts();
        $accounts = array_values(array_unique(array_filter(
            $requestedAccounts,
            fn ($account) => isset($configured[$account])
        )));

        if ($accounts === []) {
            throw new \InvalidArgumentException('Select at least one configured CrossChex biometric source.');
        }

        $jobId = (string) Str::uuid();
        $accountStats = [];

        foreach ($accounts as $account) {
            $accountStats[$account] = [
                'name' => $configured[$account]['name'],
                'fetched' => 0,
                'inserted' => 0,
                'skipped' => 0,
                'invalid' => 0,
                'pages_done' => 0,
                'page_count' => null,
                'done' => false,
            ];
        }

        $state = [
            'job_id' => $jobId,
            'state' => 'running',
            'message' => 'Preparing biometric synchronization...',
            'from' => $from->copy()->startOfDay()->toDateTimeString(),
            'to' => $to->copy()->endOfDay()->toDateTimeString(),
            'accounts' => $accounts,
            'account_index' => 0,
            'account' => $accounts[0],
            'account_name' => $configured[$accounts[0]]['name'],
            'page' => 1,
            'page_count' => null,
            'per_page' => (int) config('services.crosschex.sync.per_page', 200),
            'fetched' => 0,
            'inserted' => 0,
            'skipped' => 0,
            'invalid' => 0,
            'percent' => 0,
            'done' => false,
            'error' => null,
            'retry_after' => 0,
            'account_stats' => $accountStats,
            'started_at' => now()->toIso8601String(),
            'finished_at' => null,
        ];

        $this->putState($jobId, $state);

        return $this->publicState($state);
    }

    public function step(string $jobId): array
    {
        $lock = Cache::lock($this->lockKey($jobId), 90);

        if (! $lock->get()) {
            $state = $this->getState($jobId);

            if ($state === null) {
                throw new \RuntimeException('Biometric sync session was not found or has expired.');
            }

            $state['message'] = 'Another sync step is already processing. Retrying...';
            $state['retry_after'] = 1;

            return $this->publicState($state);
        }

        try {
            $state = $this->getState($jobId);

            if ($state === null) {
                throw new \RuntimeException('Biometric sync session was not found or has expired.');
            }

            if (($state['done'] ?? false) === true || ($state['state'] ?? null) === 'error') {
                return $this->publicState($state);
            }

            $accounts = $state['accounts'] ?? [];
            $accountIndex = (int) ($state['account_index'] ?? 0);

            if (! isset($accounts[$accountIndex])) {
                return $this->finish($jobId, $state);
            }

            $account = (string) $accounts[$accountIndex];
            $api = $this->factory->make($account);
            $page = max(1, (int) ($state['page'] ?? 1));
            $perPage = max(1, (int) ($state['per_page'] ?? 200));

            $state['account'] = $account;
            $state['account_name'] = $api->accountName();
            $state['message'] = "Syncing {$api->accountName()} - page {$page}...";
            $state['retry_after'] = 0;
            $this->putState($jobId, $state);

            $result = $this->attendanceSyncService->syncPage(
                api: $api,
                from: (string) $state['from'],
                to: (string) $state['to'],
                page: $page,
                perPage: $perPage,
            );

            if ($result['rate_limited'] === true) {
                $state['message'] = "{$api->accountName()} reached the API rate limit. Retrying automatically...";
                $state['retry_after'] = max(1, (int) $result['retry_after']);
                $this->putState($jobId, $state);

                return $this->publicState($state);
            }

            $state['page_count'] = (int) $result['page_count'];
            $state['fetched'] += (int) $result['fetched'];
            $state['inserted'] += (int) $result['inserted'];
            $state['skipped'] += (int) $result['skipped'];
            $state['invalid'] += (int) $result['invalid'];

            $stats = $state['account_stats'][$account];
            $stats['fetched'] += (int) $result['fetched'];
            $stats['inserted'] += (int) $result['inserted'];
            $stats['skipped'] += (int) $result['skipped'];
            $stats['invalid'] += (int) $result['invalid'];
            $stats['pages_done'] = max((int) $stats['pages_done'], (int) $result['page']);
            $stats['page_count'] = (int) $result['page_count'];

            $pageFinished = (int) $result['page'] >= (int) $result['page_count']
                || (int) $result['fetched'] === 0;

            if ($pageFinished) {
                $stats['done'] = true;
                $state['account_stats'][$account] = $stats;
                $state['account_index'] = $accountIndex + 1;
                $state['page'] = 1;
                $state['page_count'] = null;

                if (! isset($accounts[$state['account_index']])) {
                    return $this->finish($jobId, $state);
                }

                $nextAccount = (string) $accounts[$state['account_index']];
                $nextName = $this->factory->make($nextAccount)->accountName();
                $state['account'] = $nextAccount;
                $state['account_name'] = $nextName;
                $state['message'] = "{$api->accountName()} complete. Preparing {$nextName}...";
            } else {
                $state['account_stats'][$account] = $stats;
                $state['page'] = (int) $result['page'] + 1;
                $state['message'] = sprintf(
                    'Synced %s page %d of %d. New: %d, already saved: %d.',
                    $api->accountName(),
                    (int) $result['page'],
                    (int) $result['page_count'],
                    (int) $result['inserted'],
                    (int) $result['skipped'],
                );
            }

            $state['percent'] = $this->calculatePercent($state, $result);
            $this->putState($jobId, $state);

            return $this->publicState($state);
        } catch (\Throwable $e) {
            $state = $this->getState($jobId) ?? [
                'job_id' => $jobId,
                'state' => 'error',
                'done' => false,
                'percent' => 0,
                'inserted' => 0,
                'skipped' => 0,
                'invalid' => 0,
                'fetched' => 0,
                'account_stats' => [],
            ];

            $state['state'] = 'error';
            $state['done'] = false;
            $state['error'] = $e->getMessage();
            $state['message'] = 'Biometric synchronization failed.';
            $state['retry_after'] = 0;
            $state['finished_at'] = now()->toIso8601String();
            $this->putState($jobId, $state);

            Log::error('CrossChex browser-driven biometric sync failed.', [
                'job_id' => $jobId,
                'account' => $state['account'] ?? null,
                'page' => $state['page'] ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return $this->publicState($state);
        } finally {
            $lock->release();
        }
    }

    public function status(string $jobId): ?array
    {
        $state = $this->getState($jobId);

        return $state === null ? null : $this->publicState($state);
    }

    private function finish(string $jobId, array $state): array
    {
        $state['state'] = 'done';
        $state['done'] = true;
        $state['percent'] = 100;
        $state['page'] = null;
        $state['page_count'] = null;
        $state['retry_after'] = 0;
        $state['error'] = null;
        $state['finished_at'] = now()->toIso8601String();
        $state['message'] = sprintf(
            'Sync complete. New records: %d. Already saved/ignored: %d. Invalid skipped: %d.',
            (int) ($state['inserted'] ?? 0),
            (int) ($state['skipped'] ?? 0),
            (int) ($state['invalid'] ?? 0),
        );

        $this->putState($jobId, $state);

        Log::info('CrossChex browser-driven biometric sync completed.', [
            'job_id' => $jobId,
            'from' => $state['from'] ?? null,
            'to' => $state['to'] ?? null,
            'accounts' => $state['accounts'] ?? [],
            'inserted' => (int) ($state['inserted'] ?? 0),
            'skipped' => (int) ($state['skipped'] ?? 0),
            'invalid' => (int) ($state['invalid'] ?? 0),
        ]);

        return $this->publicState($state);
    }

    private function calculatePercent(array $state, array $result): int
    {
        $accounts = $state['accounts'] ?? [];
        $accountCount = max(1, count($accounts));
        $accountIndex = min((int) ($state['account_index'] ?? 0), $accountCount);

        // If the current account just completed, account_index already points to
        // the next account and represents exact whole-account progress.
        if (($state['page'] ?? null) === 1 && ($state['page_count'] ?? null) === null) {
            return min(99, (int) floor(($accountIndex / $accountCount) * 100));
        }

        $currentPage = max(0, (int) ($result['page'] ?? 0));
        $pageCount = max(1, (int) ($result['page_count'] ?? 1));
        $withinAccount = min(1, $currentPage / $pageCount);
        $completedAccounts = max(0, $accountIndex);

        return min(
            99,
            (int) floor((($completedAccounts + $withinAccount) / $accountCount) * 100)
        );
    }

    private function publicState(array $state): array
    {
        return [
            'jobId' => $state['job_id'] ?? null,
            'state' => $state['state'] ?? 'unknown',
            'message' => $state['message'] ?? null,
            'from' => $state['from'] ?? null,
            'to' => $state['to'] ?? null,
            'accounts' => $state['accounts'] ?? [],
            'account' => $state['account'] ?? null,
            'accountName' => $state['account_name'] ?? null,
            'page' => $state['page'] ?? null,
            'pageCount' => $state['page_count'] ?? null,
            'fetched' => (int) ($state['fetched'] ?? 0),
            'saved' => (int) ($state['inserted'] ?? 0),
            'skipped' => (int) ($state['skipped'] ?? 0),
            'invalid' => (int) ($state['invalid'] ?? 0),
            'percent' => (int) ($state['percent'] ?? 0),
            'done' => (bool) ($state['done'] ?? false),
            'error' => $state['error'] ?? null,
            'retryAfter' => (int) ($state['retry_after'] ?? 0),
            'accountStats' => $state['account_stats'] ?? [],
            'startedAt' => $state['started_at'] ?? null,
            'finishedAt' => $state['finished_at'] ?? null,
        ];
    }

    private function getState(string $jobId): ?array
    {
        $state = Cache::get($this->statusKey($jobId));

        return is_array($state) ? $state : null;
    }

    private function putState(string $jobId, array $state): void
    {
        Cache::put(
            $this->statusKey($jobId),
            $state,
            now()->addMinutes((int) config('services.crosschex.sync.session_minutes', 120))
        );
    }

    private function statusKey(string $jobId): string
    {
        return "crosschex_sync_status:{$jobId}";
    }

    private function lockKey(string $jobId): string
    {
        return "crosschex_sync_lock:{$jobId}";
    }
}
