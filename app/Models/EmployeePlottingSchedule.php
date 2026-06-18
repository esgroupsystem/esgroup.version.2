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
        'day_off',
        'remarks',
    ];

    protected $casts = [
        'work_date' => 'date',
        'grace_minutes' => 'integer',
    ];

    public function getFormattedTimeInAttribute(): string
    {
        return $this->time_in ? Carbon::parse($this->time_in)->format('H:i') : '';
    }

    public function getFormattedTimeOutAttribute(): string
    {
        return $this->time_out ? Carbon::parse($this->time_out)->format('H:i') : '';
    }

    public function getIsFlexibleAttribute(): bool
    {
        return str_contains(strtolower((string) $this->shift_name), 'flexible');
    }

    public function getIsPermanentAttribute(): bool
    {
        return is_null($this->work_date);
    }
}
