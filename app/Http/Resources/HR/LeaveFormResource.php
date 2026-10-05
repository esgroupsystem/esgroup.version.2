<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Models\LeaveRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The leave being edited in `hr/leaves/form` (`leave` header + form `values`).
 * `LeaveFormResource::blankValues()` gives the values for a new leave.
 *
 * @mixin LeaveRecord
 */
final class LeaveFormResource extends JsonResource
{
    /** @return array{leave: array<string, mixed>, values: array<string, string>} */
    public function toArray(Request $request): array
    {
        return [
            'leave' => [
                'id' => $this->id,
                'status' => ['label' => $this->statusLabel(), 'tone' => $this->statusTone()],
                'notices' => [
                    'first' => $this->first_notice_sent_at?->format('M d, Y h:i A'),
                    'second' => $this->second_notice_sent_at?->format('M d, Y h:i A'),
                    'final' => $this->final_notice_sent_at?->format('M d, Y h:i A'),
                ],
                'last_action_note' => $this->last_action_note,
            ],
            'values' => [
                'employee_id' => (string) $this->employee_id,
                'leave_type' => (string) ($this->leave_type ?? LeaveRecord::TYPES[0]),
                'start_date' => $this->start_date?->format('Y-m-d') ?? '',
                'end_date' => $this->end_date?->format('Y-m-d') ?? '',
                'reason' => (string) ($this->reason ?? ''),
            ],
        ];
    }

    /** @return array<string, string> */
    public static function blankValues(): array
    {
        return ['employee_id' => '', 'leave_type' => LeaveRecord::TYPES[0], 'start_date' => '', 'end_date' => '', 'reason' => ''];
    }
}
