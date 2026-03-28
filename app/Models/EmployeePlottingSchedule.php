<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePlottingSchedule extends Model
{
    protected $fillable = [
        'crosschex_id',
        'biometric_employee_id',
        'employee_no',
        'employee_name',
        'work_date',
        'shift_name',
        'time_in',
        'time_out',
        'grace_minutes',
        'status',
        'remarks',
    ];

    protected $casts = [
        'work_date' => 'date',
        'grace_minutes' => 'integer',
    ];
}
