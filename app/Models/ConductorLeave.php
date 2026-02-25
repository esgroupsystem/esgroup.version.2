<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConductorLeave extends Model
{
    protected $table = 'conductor_leaves';

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'days',
        'reason',
        'offense_level',
        'status',
        'last_action_note',
        'first_notice_sent_at',
        'second_notice_sent_at',
        'final_notice_sent_at',
    ];

    protected $casts = [
        'first_notice_sent_at' => 'datetime',
        'second_notice_sent_at' => 'datetime',
        'final_notice_sent_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
