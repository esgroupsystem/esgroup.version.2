<?php

namespace App\Console\Commands;

use App\Services\Payroll\DailyAttendanceSummaryService;
use Illuminate\Console\Command;

class BuildDailyAttendanceSummary extends Command
{
    protected $signature = 'attendance:build-summary {start_date} {end_date?}';

    protected $description = 'Build daily attendance summary for date or date range';

    public function handle(DailyAttendanceSummaryService $service): int
    {
        $startDate = $this->argument('start_date');
        $endDate = $this->argument('end_date') ?: $startDate;

        $this->info("Building attendance summaries from {$startDate} to {$endDate}...");

        $service->buildForPeriod($startDate, $endDate);

        $this->info('Attendance summary build completed.');

        return self::SUCCESS;
    }
}
