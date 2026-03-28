<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollAttendanceAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'biometric_employee_id',
        'employee_no',
        'employee_name',
        'work_date',
        'adjustment_type',
        'adjusted_time_in',
        'adjusted_time_out',
        'adjusted_day_type',
        'is_paid',
        'ignore_late',
        'ignore_undertime',
        'reason',
        'remarks',
        'encoded_by',
        'encoded_at',
    ];

    protected $casts = [
        'work_date' => 'date',
        'is_paid' => 'boolean',
        'ignore_late' => 'boolean',
        'ignore_undertime' => 'boolean',
        'encoded_at' => 'datetime',
    ];

    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }
}
