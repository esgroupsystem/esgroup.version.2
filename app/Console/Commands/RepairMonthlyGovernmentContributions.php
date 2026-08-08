<?php

namespace App\Console\Commands;

use App\Models\Payroll;
use App\Services\Payroll\BenefitContributionPostingService;
use App\Services\Payroll\MonthlyGovernmentReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class RepairMonthlyGovernmentContributions extends Command
{
    protected $signature = 'payroll:repair-monthly-government
        {--year= : Contribution year}
        {--month= : Contribution month 1-12}
        {--group= : Payroll group, e.g. 1 or 2}
        {--apply : Persist the repair. Without this flag the command is a dry run.}';

    protected $description = 'Reconcile a finalized monthly government contribution cycle using business 1st + 2nd cutoff payroll gross and rebuild Benefits Records.';

    public function handle(
        MonthlyGovernmentReconciliationService $reconciliationService,
        BenefitContributionPostingService $postingService
    ): int {
        $year = (int) $this->option('year');
        $month = (int) $this->option('month');
        $group = (int) $this->option('group');
        $apply = (bool) $this->option('apply');

        if ($year < 2020 || $year > 2100 || $month < 1 || $month > 12 || ! in_array($group, [1, 2], true)) {
            $this->error('Required: --year=YYYY --month=1..12 --group=1|2 [--apply].');

            return self::INVALID;
        }

        $closingPayroll = Payroll::query()
            ->where('contribution_year', $year)
            ->where('contribution_month', $month)
            ->where('garage_group', (string) $group)
            ->where('cutoff_type', 'first')
            ->where('status', 'finalized')
            ->latest('id')
            ->first();

        if (! $closingPayroll) {
            $this->error('No finalized business 2nd cutoff (11-25) payroll was found for the requested contribution month/group.');

            return self::FAILURE;
        }

        DB::beginTransaction();

        try {
            $lockedPayroll = Payroll::query()
                ->lockForUpdate()
                ->findOrFail($closingPayroll->id);

            $result = $reconciliationService->reconcileClosingCutoff(
                $lockedPayroll,
                true,
                $apply ? 'artisan_monthly_repair' : 'artisan_monthly_repair_dry_run'
            );

            $rows = collect($result['changes'])->map(function (array $change): array {
                return [
                    $change['employee_name'],
                    number_format((float) $change['business_first_gross'], 2),
                    number_format((float) $change['business_second_gross'], 2),
                    number_format((float) $change['monthly_gross'], 2),
                    number_format((float) $change['sss_msc'], 2),
                    number_format((float) $change['sss_mpf_msc'], 2),
                    number_format((float) data_get($change, 'old.sss_employee', 0), 2),
                    number_format((float) data_get($change, 'new.sss_employee', 0), 2),
                    number_format((float) data_get($change, 'old.net_pay', 0), 2),
                    number_format((float) data_get($change, 'new.net_pay', 0), 2),
                ];
            })->all();

            $this->table([
                'Employee',
                '1st Gross',
                '2nd Gross',
                'Monthly Gross',
                'SSS MSC',
                'MPF MSC',
                'Old SSS EE',
                'New SSS EE',
                'Old Net',
                'New Net',
            ], $rows);

            if (! $apply) {
                DB::rollBack();
                $this->warn('DRY RUN ONLY. No payroll or Benefits Records were changed. Re-run with --apply after reviewing the values.');

                return self::SUCCESS;
            }

            $posted = $postingService->postForPayroll(
                $lockedPayroll->fresh(),
                $lockedPayroll->finalized_by,
                now('Asia/Manila')
            );

            DB::commit();

            $this->info(sprintf(
                'Repair applied. %d payroll item(s) reconciled and %d exact monthly Benefits Record(s) rebuilt.',
                (int) $result['updated_items'],
                $posted
            ));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            DB::rollBack();
            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
