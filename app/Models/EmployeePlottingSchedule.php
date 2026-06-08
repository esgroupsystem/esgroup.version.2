<?php

namespace App\Models;

use Carbon\Carbon;
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

    // Make work_date a Carbon instance
    protected $dates = ['work_date'];

    protected $casts = [
        'grace_minutes' => 'integer',
    ];

    // Safely parse time in/out
    public function getFormattedTimeInAttribute()
    {
        return $this->time_in ? Carbon::parse($this->time_in)->format('H:i') : '';
    }

    public function getFormattedTimeOutAttribute()
    {
        return $this->time_out ? Carbon::parse($this->time_out)->format('H:i') : '';
    }
}
