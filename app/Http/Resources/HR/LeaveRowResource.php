<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Enums\LeaveKind;
use App\Models\LeaveRecord;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of a leave list (`hr/leaves/index`). `remaining` counts down the leave, then
 * warns as the days after it pass: Ready for Duty (day 1), Warning for 1st Notice (≤ 9),
 * Warning for 2nd Notice (≤ 22), Subject for Final Notice. Load `employee.position` first.
 *
 * @mixin LeaveRecord
 */
final class LeaveRowResource extends JsonResource
{
    private const CLOSED = ['cancelled', 'completed', 'terminated'];

    public function __construct(
        LeaveRecord $leave,
        private readonly LeaveKind $kind,
        private readonly CarbonInterface $today,
        private readonly bool $canUpdate,
    ) {
        parent::__construct($leave);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $end = $this->end_date?->copy()->timezone('Asia/Manila')->startOfDay();
        $route = $this->kind->routeName();
        $notice = fn (string $type, ?CarbonInterface $sentAt): array => [
            'sent_at' => $sentAt?->copy()->timezone('Asia/Manila')->format('M d, Y h:i A'),
            'proof_url' => $this->proofPath($type) ? route("{$route}.proof", [$this->id, $type]) : null,
        ];

        return [
            'id' => $this->id,
            'employee' => $this->employee ? LeaveEmployeeResource::make($this->employee)->resolve($request) : null,
            'leave_type' => $this->leave_type,
            'reason' => $this->reason,
            'start_date' => $this->start_date?->format('M d, Y'),
            'end_date' => $this->end_date?->format('M d, Y'),
            'days' => (int) ($this->days ?? 0),
            'notices' => [
                'first' => $notice('first', $this->first_notice_sent_at),
                'second' => $notice('second', $this->second_notice_sent_at),
                'final' => $notice('final', $this->final_notice_sent_at),
            ],
            'status' => ['label' => $this->statusLabel(), 'tone' => $this->statusTone()],
            'remaining' => $this->remaining($end),
            'ready_at' => $this->ready_for_duty_notified_at?->copy()->timezone('Asia/Manila')->format('M d, Y h:i A'),
            'last_action_note' => $this->last_action_note,
            'locked' => in_array(strtolower((string) ($this->status ?? '')), self::CLOSED, true),
            'after_leave' => $end !== null && $this->today->gt($end),
            'can_update' => $this->canUpdate,
            'edit_url' => route("{$route}.edit", $this->id),
            'action_url' => route("{$route}.action", $this->id),
        ];
    }

    /** @return array{label: string, tone: string} */
    private function remaining(?CarbonInterface $end): array
    {
        if ($end === null || in_array(strtolower((string) ($this->status ?? '')), self::CLOSED, true)) {
            return ['label' => $this->statusLabel(), 'tone' => $this->statusTone()];
        }

        if ($this->today->lte($end)) {
            $days = (int) $this->today->diffInDays($end) + 1;

            return ['label' => 'On Leave: '.$days.' '.($days === 1 ? 'day' : 'days').' left', 'tone' => 'success'];
        }

        $daysAfterEnd = (int) $end->diffInDays($this->today);

        return match (true) {
            $daysAfterEnd === 1 => ['label' => 'Ready for Duty', 'tone' => 'primary'],
            $daysAfterEnd <= 9 => ['label' => 'Warning for 1st Notice', 'tone' => 'info'],
            $daysAfterEnd <= 22 => ['label' => 'Warning for 2nd Notice', 'tone' => 'warning'],
            default => ['label' => 'Subject for Final Notice', 'tone' => 'danger'],
        };
    }
}
