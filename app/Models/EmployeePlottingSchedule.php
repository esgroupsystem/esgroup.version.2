<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePlottingSchedule extends Model
{
    protected $fillable = [
        'employee_biometric_id',
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
        'employee_biometric_id' => 'integer',
        'work_date' => 'date',
        'grace_minutes' => 'integer',
    ];

    public function employeeBiometric(): BelongsTo
    {
        return $this->belongsTo(EmployeeBiometric::class, 'employee_biometric_id');
    }

    public function scopePermanent(Builder $query): Builder
    {
        return $query->whereNull('work_date');
    }

    public function scopeForPayrollActiveEmployees(Builder $query): Builder
    {
        return $query->whereHas('employeeBiometric', function (Builder $query): void {
            $query->payrollActive();
        });
    }

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
