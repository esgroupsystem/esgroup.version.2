<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAttendanceSummary extends Model
{
    protected $fillable = [
        'employee_biometric_id',
        'employee_id',
        'biometric_employee_id',
        'employee_no',
        'employee_name',
        'work_date',

        'plotting_schedule_id',
        'attendance_adjustment_id',
        'holiday_id',

        'crosschex_id',
        'shift_name',
        'scheduled_time_in',
        'scheduled_time_out',
        'grace_minutes',
        'schedule_status',
        'schedule_remarks',

        'actual_time_in',
        'actual_time_out',
        'raw_log_count',
        'has_biometrics',
        'first_log_state',
        'last_log_state',

        'is_rest_day',
        'is_leave',
        'is_holiday',
        'holiday_name',
        'holiday_type',
        'holiday_worked_multiplier',
        'holiday_not_worked_multiplier',

        'has_adjustment',
        'adjustment_type',
        'adjusted_time_in',
        'adjusted_time_out',
        'adjusted_day_type',
        'adjustment_is_paid',
        'ignore_late',
        'ignore_undertime',
        'adjustment_reason',
        'adjustment_remarks',

        'attendance_status',
        'late_minutes',
        'undertime_minutes',
        'worked_minutes',
        'overtime_minutes',
        'payable_days',
        'payable_hours',

        'is_absent',
        'is_incomplete_log',

        'remarks',
        'computed_at',
        'meta',
    ];

    protected $casts = [
        'employee_biometric_id' => 'integer',
        'employee_id' => 'integer',
        'plotting_schedule_id' => 'integer',
        'attendance_adjustment_id' => 'integer',
        'holiday_id' => 'integer',
        'work_date' => 'date',
        'actual_time_in' => 'datetime',
        'actual_time_out' => 'datetime',

        'raw_log_count' => 'integer',
        'has_biometrics' => 'boolean',
        'is_rest_day' => 'boolean',
        'is_leave' => 'boolean',
        'is_holiday' => 'boolean',
        'has_adjustment' => 'boolean',
        'adjustment_is_paid' => 'boolean',
        'ignore_late' => 'boolean',
        'ignore_undertime' => 'boolean',
        'is_absent' => 'boolean',
        'is_incomplete_log' => 'boolean',

        'grace_minutes' => 'integer',
        'late_minutes' => 'integer',
        'undertime_minutes' => 'integer',
        'worked_minutes' => 'integer',
        'overtime_minutes' => 'integer',

        'payable_days' => 'decimal:2',
        'payable_hours' => 'decimal:2',
        'holiday_worked_multiplier' => 'decimal:2',
        'holiday_not_worked_multiplier' => 'decimal:2',

        'computed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function employeeBiometric(): BelongsTo
    {
        return $this->belongsTo(EmployeeBiometric::class, 'employee_biometric_id');
    }

    public function plottingSchedule(): BelongsTo
    {
        return $this->belongsTo(EmployeePlottingSchedule::class, 'plotting_schedule_id');
    }

    public function attendanceAdjustment(): BelongsTo
    {
        return $this->belongsTo(PayrollAttendanceAdjustment::class, 'attendance_adjustment_id');
    }

    public function holiday(): BelongsTo
    {
        return $this->belongsTo(Holiday::class, 'holiday_id');
    }

    public function scopeForEmployeeBiometric(Builder $query, int $employeeBiometricId): Builder
    {
        return $query->where('employee_biometric_id', $employeeBiometricId);
    }

    public function scopeForPayrollActiveEmployees(Builder $query): Builder
    {
        return $query->whereHas('employeeBiometric', function (Builder $query): void {
            $query->payrollActive();
        });
    }
}
