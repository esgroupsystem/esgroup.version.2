<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceDailySummary extends Model
{
    protected $fillable = [
        'payroll_employee_id',
        'work_date',
        'time_in',
        'time_out',
        'worked_minutes',
        'late_minutes',
        'undertime_minutes',
        'overtime_minutes',
        'status',
        'remarks',
    ];

    protected $casts = [
        'work_date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    public function payrollEmployee()
    {
        return $this->belongsTo(PayrollEmployee::class);
    }
}
