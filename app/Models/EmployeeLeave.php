<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeave extends Model
{
    protected $table = 'employee_leaves';

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

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isClosed(): bool
    {
        return in_array(
            strtolower((string) $this->status),
            ['cancelled', 'completed', 'terminated'],
            true
        );
    }
}
