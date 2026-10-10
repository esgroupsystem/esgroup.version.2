<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Payroll\PayrollDataResetService;
use Illuminate\Console\Command;

/**
 * Removes payroll TEST data (including finalized payrolls, which the app cannot delete).
 * Settings, Employee Rates, schedules, holidays and employees are kept.
 */
final class ResetPayrollData extends Command
{
    protected $signature = 'payroll:reset-data
        {--adjustments : Also delete every Payroll Adjustment (OT, leave, offset, holiday...) and their uploaded OT forms}
        {--summaries : Also delete every Attendance Summary row (rebuild later with attendance:build-summary)}
        {--dry-run : Only show what would be deleted}';

    protected $description = 'Delete all payroll runs and what they saved (Benefits Records, payment and transaction logs). Command line only.';

    public function handle(PayrollDataResetService $service): int
    {
        $scopes = array_keys(array_filter([
            'adjustments' => (bool) $this->option('adjustments'),
            'summaries' => (bool) $this->option('summaries'),
        ]));

        $this->line('Database: <comment>'.config('database.connections.'.config('database.default').'.database').'</comment>');
        $this->table(['Table', 'Rows'], collect($service->preview($scopes))->map(fn (int $rows, string $table): array => [$table, $rows])->values()->all());
        $this->line('Kept: employees, biometrics, schedules, Employee Rates, holidays, Payroll Settings, users.');

        if ($this->option('dry-run')) {
            $this->info('Dry run: nothing was deleted.');

            return self::SUCCESS;
        }

        if ($this->ask('This cannot be undone. Make a database backup first. Type DELETE to continue') !== 'DELETE') {
            $this->info('Cancelled. Nothing was deleted.');

            return self::SUCCESS;
        }

        $removed = $service->run($scopes);

        $this->table(['Removed', 'Rows'], collect($removed)->map(fn (int $rows, string $table): array => [$table, $rows])->values()->all());
        $this->info('Payroll data deleted.');

        return self::SUCCESS;
    }
}
