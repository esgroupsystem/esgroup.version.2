<?php

namespace App\Console\Commands;

use App\Models\DriverLeave;
use App\Services\LeaveNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessDriverLeaveReadyForDuty extends Command
{
    protected $signature = 'leaves:driver-ready-for-duty';

    protected $description = 'Notify HR Officers about driver leave return and escalation notices';

    public function handle(LeaveNotificationService $notificationService)
    {
        $today = now('Asia/Manila')->startOfDay();

        $leaves = DriverLeave::with('employee')
            ->whereNotNull('end_date')
            ->whereIn('status', ['approved', 'active', 'on_leave'])
            ->get();

        foreach ($leaves as $leave) {
            if (empty($leave->end_date)) {
                $this->line("Driver Leave ID {$leave->id}: skipped, no end_date");

                continue;
            }

            $end = Carbon::parse($leave->end_date)->timezone('Asia/Manila')->startOfDay();

            if ($today->lte($end)) {
                $this->line("Driver Leave ID {$leave->id}: skipped, today <= end_date");

                continue;
            }

            $daysAfterEnd = $end->diffInDays($today);

            $this->line("Driver Leave ID {$leave->id}");
            $this->line('Employee: '.($leave->employee->full_name ?? 'N/A'));
            $this->line('End date: '.$end->toDateString());
            $this->line('Today: '.$today->toDateString());
            $this->line("Days after end: {$daysAfterEnd}");
            $this->line('ready_for_duty_notified_at: '.($leave->ready_for_duty_notified_at ?? 'NULL'));
            $this->line('first_notice_sent_at: '.($leave->first_notice_sent_at ?? 'NULL'));
            $this->line('second_notice_sent_at: '.($leave->second_notice_sent_at ?? 'NULL'));
            $this->line('final_notice_sent_at: '.($leave->final_notice_sent_at ?? 'NULL'));

            // Ready for Duty
            if ($daysAfterEnd == 1 && is_null($leave->ready_for_duty_notified_at)) {
                $this->info("Sending Ready for Duty for driver leave ID {$leave->id}");

                $notificationService->sendToHrOfficers(
                    $leave->fresh('employee'),
                    'Ready for Duty',
                    'Driver'
                );

                $leave->update([
                    'ready_for_duty_notified_at' => now('Asia/Manila'),
                    'last_action_note' => 'Ready for Duty email sent to HR',
                ]);

                continue;
            }

            // 1st Notice
            if ($daysAfterEnd >= 2 && $daysAfterEnd <= 9 && is_null($leave->first_notice_sent_at)) {
                $this->info("Sending 1st Notice for driver leave ID {$leave->id}");

                $notificationService->sendToHrOfficers(
                    $leave->fresh('employee'),
                    '1st Notice',
                    'Driver'
                );

                $leave->update([
                    'first_notice_sent_at' => now('Asia/Manila'),
                    'last_action_note' => '1st Notice email sent to HR',
                ]);

                continue;
            }

            // 2nd Notice
            if ($daysAfterEnd >= 10 && $daysAfterEnd <= 22 && is_null($leave->second_notice_sent_at)) {
                $this->info("Sending 2nd Notice for driver leave ID {$leave->id}");

                $notificationService->sendToHrOfficers(
                    $leave->fresh('employee'),
                    '2nd Notice',
                    'Driver'
                );

                $leave->update([
                    'second_notice_sent_at' => now('Asia/Manila'),
                    'last_action_note' => '2nd Notice email sent to HR',
                ]);

                continue;
            }

            // 3rd Notice / Subject for Termination
            if ($daysAfterEnd >= 23 && is_null($leave->final_notice_sent_at)) {
                $this->info("Sending 3rd Notice / Subject for Termination for driver leave ID {$leave->id}");

                $notificationService->sendToHrOfficers(
                    $leave->fresh('employee'),
                    '3rd Notice / Subject for Termination',
                    'Driver'
                );

                $leave->update([
                    'final_notice_sent_at' => now('Asia/Manila'),
                    'last_action_note' => '3rd Notice / Subject for Termination email sent to HR',
                ]);

                continue;
            }

            $this->line("Driver Leave ID {$leave->id}: no notice triggered");
            $this->line(str_repeat('-', 50));
        }

        $this->info('Driver leave notices processed.');

        return Command::SUCCESS;
    }
}
