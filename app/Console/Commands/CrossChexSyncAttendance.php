<?php

namespace App\Console\Commands;

use App\Services\Biometrics\CrossChexAttendanceSyncService;
use App\Services\CrossChexServiceFactory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrossChexSyncAttendance extends Command
{
    protected $signature = 'crosschex:sync
                            {--from= : Start date/time}
                            {--to= : End date/time}
                            {--account=* : CrossChex account key; omit to sync all configured accounts}
                            {--debug=0 : Show per-page synchronization details}';

    protected $description = 'Synchronize CrossChex attendance logs using the same fast duplicate-safe engine as Biometrics Sync';

    public function handle(
        CrossChexServiceFactory $factory,
        CrossChexAttendanceSyncService $syncService,
    ): int {
        $configured = $factory->configuredAccounts();
        $requested = array_values(array_filter((array) $this->option('account')));
        $accounts = $requested !== [] ? $requested : array_keys($configured);

        if ($accounts === []) {
            $this->error('No CrossChex accounts are fully configured.');

            return self::FAILURE;
        }

        foreach ($accounts as $account) {
            if (! isset($configured[$account])) {
                $this->error("CrossChex account [{$account}] is not configured.");

                return self::FAILURE;
            }
        }

        $fromOption = trim((string) $this->option('from'));
        $toOption = trim((string) $this->option('to'));
        $manualRange = $fromOption !== '' || $toOption !== '';

        if ($manualRange && ($fromOption === '' || $toOption === '')) {
            $this->error('When using a manual range, both --from and --to are required.');

            return self::FAILURE;
        }

        $grandInserted = 0;
        $grandSkipped = 0;
        $grandInvalid = 0;

        foreach ($accounts as $account) {
            $api = $factory->make($account);
            $to = $manualRange
                ? Carbon::parse($toOption)->endOfDay()
                : now();

            $from = $manualRange
                ? Carbon::parse($fromOption)->startOfDay()
                : $this->automaticFrom($account);

            $this->newLine();
            $this->info("Syncing {$api->accountName()} from {$from->toDateTimeString()} to {$to->toDateTimeString()}");

            $page = 1;
            $perPage = (int) config('services.crosschex.sync.per_page', 200);
            $inserted = 0;
            $skipped = 0;
            $invalid = 0;

            while (true) {
                $result = $syncService->syncPage(
                    api: $api,
                    from: $from->toDateTimeString(),
                    to: $to->toDateTimeString(),
                    page: $page,
                    perPage: $perPage,
                );

                if ($result['rate_limited']) {
                    $wait = max(1, (int) $result['retry_after']);
                    $this->warn("Rate limit reached. Retrying page {$page} in {$wait} seconds...");
                    sleep($wait);
                    continue;
                }

                $inserted += (int) $result['inserted'];
                $skipped += (int) $result['skipped'];
                $invalid += (int) $result['invalid'];

                if ((int) $this->option('debug') === 1) {
                    $this->line(sprintf(
                        'Page %d/%d | fetched=%d new=%d ignored=%d invalid=%d',
                        $result['page'],
                        $result['page_count'],
                        $result['fetched'],
                        $result['inserted'],
                        $result['skipped'],
                        $result['invalid'],
                    ));
                }

                if ((int) $result['page'] >= (int) $result['page_count'] || (int) $result['fetched'] === 0) {
                    break;
                }

                $page = (int) $result['page'] + 1;
            }

            if (! $manualRange) {
                $this->saveAutomaticLastSync($account, $to);
            }

            $grandInserted += $inserted;
            $grandSkipped += $skipped;
            $grandInvalid += $invalid;

            $this->info(
                "{$api->accountName()} complete. New: {$inserted}, already saved: {$skipped}, invalid: {$invalid}."
            );
        }

        $this->newLine();
        $this->info(
            "All selected sources complete. New: {$grandInserted}, already saved: {$grandSkipped}, invalid: {$grandInvalid}."
        );

        return self::SUCCESS;
    }

    private function automaticFrom(string $account): Carbon
    {
        $key = "crosschex_last_sync:{$account}";
        $value = DB::table('settings')->where('key', $key)->value('value');

        if (! filled($value)) {
            return now()->subDay();
        }

        return Carbon::parse((string) $value);
    }

    private function saveAutomaticLastSync(string $account, Carbon $to): void
    {
        $key = "crosschex_last_sync:{$account}";

        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => $to->toDateTimeString(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
