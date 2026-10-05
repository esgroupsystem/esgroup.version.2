<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\LeaveKind;
use App\Services\HR\LeaveReminderService;
use Illuminate\Console\Command;

/** Scheduled in routes/console.php; the work is done by LeaveReminderService. */
final class ProcessEmployeeLeaveReadyForDuty extends Command
{
    protected $signature = 'leaves:employee-ready-for-duty';

    protected $description = 'Notify HR Officers about leave return and escalation notices';

    public function handle(LeaveReminderService $reminders): int
    {
        $reminders->process(LeaveKind::Employee, fn (string $level, string $message) => $this->{$level}($message));

        return self::SUCCESS;
    }
}
