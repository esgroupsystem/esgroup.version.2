<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'second_notice_sent_at',
        'final_notice_sent_at',
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
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}