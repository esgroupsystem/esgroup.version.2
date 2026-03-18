<?php

namespace App\Console\Commands;

use App\Services\LeaveNotificationService;
use Illuminate\Console\Command;

class ProcessAllLeaveNotices extends Command
{
    protected $signature = 'leaves:process-all';

    protected $description = 'Process all leave notices for employees, drivers, and conductors';

    public function handle(LeaveNotificationService $service)
    {
        app(\App\Console\Commands\ProcessEmployeeLeaveReadyForDuty::class)->handle($service);
        app(\App\Console\Commands\ProcessDriverLeaveReadyForDuty::class)->handle($service);
        app(\App\Console\Commands\ProcessConductorLeaveReadyForDuty::class)->handle($service);

        $this->info('All leave notices processed.');

        return Command::SUCCESS;
    }
}
