<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_id',
        'employee_id',
        'biometric_employee_id',
        'employee_no',
        'employee_name',
        'monthly_rate',
        'daily_rate',
        'hourly_rate',
        'minute_rate',
        'total_worked_days',
        'total_payable_days',
        'total_payable_hours',
        'total_worked_minutes',
        'total_late_minutes',
        'total_undertime_minutes',
        'total_overtime_minutes',
        'total_absent_days',
        'total_rest_day_worked',
        'total_holiday_worked',
        'total_leave_days',
        'gross_pay',
        'late_deduction',
        'undertime_deduction',
        'absence_deduction',
        'overtime_pay',
        'holiday_pay',
        'rest_day_pay',
        'leave_pay',
        'taxable_compensation',
        'sss_employee',
        'sss_employer',
        'philhealth_employee',
        'philhealth_employer',
        'pagibig_employee',
        'pagibig_employer',
        'withholding_tax',
        'total_employee_government_deductions',
        'total_employer_government_contributions',
        'other_additions',
        'other_deductions',
        'net_pay',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
}
