<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\LeaveKind;
use App\Services\HR\LeaveReminderService;
use Illuminate\Console\Command;

/** Scheduled in routes/console.php; the work is done by LeaveReminderService. */
final class ProcessConductorLeaveReadyForDuty extends Command
{
    protected $signature = 'leaves:conductor-ready-for-duty';

    protected $description = 'Notify HR Officers about Conductor Leave return and escalation notices';

    public function handle(LeaveReminderService $reminders): int
    {
        $reminders->process(LeaveKind::Conductor, fn (string $level, string $message) => $this->{$level}($message));

        return self::SUCCESS;
    }
}
