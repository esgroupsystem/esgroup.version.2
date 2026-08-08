<?php

namespace App\Console\Commands;

use App\Models\Payroll;
use App\Services\Payroll\BenefitContributionPostingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class BackfillBenefitContributionRecords extends Command
{
    protected $signature = 'benefits:backfill-finalized
        {--year= : Contribution year to backfill}
        {--month= : Contribution month (1-12) to backfill}';

    protected $description = 'Create Benefits Records for payrolls that were already finalized before the Benefits Records module was installed.';

    public function handle(BenefitContributionPostingService $postingService): int
    {
        $year = $this->option('year');
        $month = $this->option('month');

        if ($month !== null && (! is_numeric($month) || (int) $month < 1 || (int) $month > 12)) {
            $this->error('--month must be between 1 and 12.');

            return self::INVALID;
        }

        if ($year !== null && (! is_numeric($year) || (int) $year < 2020 || (int) $year > 2100)) {
            $this->error('--year must be a valid four-digit contribution year.');

            return self::INVALID;
        }

        $query = Payroll::query()
            ->where('status', 'finalized')
            ->when($year !== null, fn ($query) => $query->where('contribution_year', (int) $year))
            ->when($month !== null, fn ($query) => $query->where('contribution_month', (int) $month))
            ->orderBy('id');

        $payrollCount = (clone $query)->count();

        if ($payrollCount === 0) {
            $this->warn('No finalized payrolls matched the requested period.');

            return self::SUCCESS;
        }

        $this->info("Backfilling {$payrollCount} finalized payroll(s)...");

        $recordCount = 0;
        $failureCount = 0;

        $query->chunkById(25, function ($payrolls) use ($postingService, &$recordCount, &$failureCount): void {
            foreach ($payrolls as $payroll) {
                try {
                    $posted = DB::transaction(function () use ($payroll, $postingService): int {
                        $lockedPayroll = Payroll::query()
                            ->lockForUpdate()
                            ->findOrFail($payroll->id);

                        return $postingService->postForPayroll(
                            $lockedPayroll,
                            $lockedPayroll->finalized_by,
                            $lockedPayroll->finalized_at ?? now('Asia/Manila')
                        );
                    });

                    $recordCount += $posted;
                    $this->line("✓ {$payroll->payroll_number}: {$posted} record(s)");
                } catch (Throwable $exception) {
                    $failureCount++;
                    report($exception);
                    $this->error("✗ {$payroll->payroll_number}: {$exception->getMessage()}");
                }
            }
        });

        $this->newLine();
        $this->info("Backfill complete. {$recordCount} Benefits Record(s) processed.");

        if ($failureCount > 0) {
            $this->warn("{$failureCount} payroll(s) failed. Check laravel.log before retrying.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
