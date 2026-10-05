<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeaveStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared columns and behaviour of the three leave tables
 * (employee_leaves, driver_leaves, conductor_leaves). See App\Enums\LeaveKind.
 *
 * @property int $id
 * @property int|string|null $employee_id
 * @property string|null $leave_type
 * @property \Carbon\CarbonInterface|null $start_date
 * @property \Carbon\CarbonInterface|null $end_date
 * @property int|null $days
 * @property string|null $reason
 * @property int|null $offense_level
 * @property \Carbon\CarbonInterface|null $first_notice_sent_at
 * @property string|null $first_notice_proof
 * @property \Carbon\CarbonInterface|null $second_notice_sent_at
 * @property string|null $second_notice_proof
 * @property \Carbon\CarbonInterface|null $final_notice_sent_at
 * @property string|null $final_notice_proof
 * @property string|null $status
 * @property string|null $last_action_note
 * @property \Carbon\CarbonInterface|null $ready_for_duty_notified_at
 * @property-read Employee|null $employee
 */
abstract class LeaveRecord extends Model
{
    /** Leave types offered in the form. */
    public const TYPES = ['Medical Leave', 'Emergency Leave', 'Vacation Leave', 'Others'];

    /** Notice proof route keys => proof column. */
    public const PROOFS = [
        'first' => 'first_notice_proof',
        'second' => 'second_notice_proof',
        'final' => 'final_notice_proof',
    ];

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'days',
        'reason',
        'offense_level',
        'first_notice_sent_at',
        'first_notice_proof',
        'second_notice_sent_at',
        'second_notice_proof',
        'final_notice_sent_at',
        'final_notice_proof',
        'status',
        'last_action_note',
        'ready_for_duty_notified_at',
    ];

    protected $casts = [
        'first_notice_sent_at' => 'datetime',
        'second_notice_sent_at' => 'datetime',
        'final_notice_sent_at' => 'datetime',
        'ready_for_duty_notified_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'days' => 'integer',
        'offense_level' => 'integer',
    ];

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isClosed(): bool
    {
        $status = LeaveStatus::tryFrom(ucfirst(strtolower((string) $this->status)));

        return $status?->isClosed() ?? false;
    }

    /** "Active" when blank, otherwise the stored status with a capital first letter. */
    public function statusLabel(): string
    {
        return $this->status ? ucfirst((string) $this->status) : 'Active';
    }

    /** Badge tone for the status (lib/employee-status tones on the React side). */
    public function statusTone(): string
    {
        return match (strtolower((string) ($this->status ?? ''))) {
            'completed' => 'success',
            'cancelled' => 'secondary',
            'terminated' => 'danger',
            'inactive' => 'warning',
            default => 'primary',
        };
    }

    /** Stored proof path for "first", "second" or "final"; null when none. */
    public function proofPath(string $type): ?string
    {
        $column = self::PROOFS[$type] ?? null;

        return $column ? $this->getAttribute($column) : null;
    }
}
