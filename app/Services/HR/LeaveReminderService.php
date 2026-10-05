<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Enums\LeaveKind;
use App\Mail\LeaveNoticeMail;
use App\Models\LeaveRecord;
use App\Models\User;
use App\Repositories\Contracts\HR\LeaveRepositoryInterface;
use App\Repositories\Contracts\Security\UserRepositoryInterface;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Mail;

/**
 * The scheduled `leaves:<kind>-ready-for-duty` commands: once a leave has ended, email the
 * HR officers "Ready for Duty" (day 1), then the 1st (days 2–9), 2nd (10–22) and
 * 3rd / termination (23+) reminders, each once.
 */
final class LeaveReminderService
{
    private const OPEN_STATUSES = ['approved', 'active', 'on_leave'];

    private const RECIPIENT_ROLES = ['HR Officer', 'Developer'];

    /** Reminder => [first day after the end date, last day (null = no limit), column stamped, label]. */
    private const STEPS = [
        ['from' => 1, 'to' => 1, 'column' => 'ready_for_duty_notified_at', 'label' => 'Ready for Duty'],
        ['from' => 2, 'to' => 9, 'column' => 'first_notice_sent_at', 'label' => '1st Notice'],
        ['from' => 10, 'to' => 22, 'column' => 'second_notice_sent_at', 'label' => '2nd Notice'],
        ['from' => 23, 'to' => null, 'column' => 'final_notice_sent_at', 'label' => '3rd Notice / Subject for Termination'],
    ];

    public function __construct(
        private readonly LeaveRepositoryInterface $leaves,
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * @param  Closure(string, string): void  $output  receives (level "line"|"info", message)
     * @return int reminders sent
     */
    public function process(LeaveKind $kind, Closure $output): int
    {
        $today = now('Asia/Manila')->startOfDay();
        $label = $kind->reminderLabel();
        $sent = 0;

        foreach ($this->leaves->withEndDateInStatus($kind, self::OPEN_STATUSES) as $leave) {
            $end = Carbon::parse($leave->end_date)->timezone('Asia/Manila')->startOfDay();
            if ($today->lte($end)) {
                $output('line', "{$label} ID {$leave->id}: skipped, today <= end_date");

                continue;
            }

            $daysAfterEnd = (int) $end->diffInDays($today);
            $output('line', "{$label} ID {$leave->id}: employee ".($leave->employee->full_name ?? 'N/A').", ended {$end->toDateString()}, {$daysAfterEnd} day(s) ago");

            $step = $this->dueStep($leave, $daysAfterEnd);
            if ($step === null) {
                $output('line', "{$label} ID {$leave->id}: no notice triggered");

                continue;
            }

            $output('info', "Sending {$step['label']} for ".strtolower($label)." ID {$leave->id}");
            $this->notifyHr($leave->fresh('employee') ?? $leave, $step['label'], $kind->noun());
            $this->leaves->update($leave, [
                $step['column'] => now('Asia/Manila'),
                'last_action_note' => "{$step['label']} email sent to HR",
            ]);
            $sent++;
        }

        $output('info', "{$kind->noun()} leave notices processed.");

        return $sent;
    }

    /** @return array{from: int, to: int|null, column: string, label: string}|null */
    private function dueStep(LeaveRecord $leave, int $daysAfterEnd): ?array
    {
        foreach (self::STEPS as $step) {
            $inRange = $daysAfterEnd >= $step['from'] && ($step['to'] === null || $daysAfterEnd <= $step['to']);
            if ($inRange && $leave->getAttribute($step['column']) === null) {
                return $step;
            }
        }

        return null;
    }

    private function notifyHr(LeaveRecord $leave, string $noticeType, string $category): void
    {
        $this->users->withRoles(self::RECIPIENT_ROLES)
            ->filter(fn (User $user): bool => filled($user->email))
            ->each(fn (User $user) => Mail::to($user->email)->send(new LeaveNoticeMail($leave, $noticeType, $category)));
    }
}
