<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_id',
        'employee_biometric_id',
        'employee_id',
        'payroll_employee_salary_id',
        'biometric_employee_id',
        'employee_no',
        'employee_name',
        'crosschex_id',
        'rate_type',
        'monthly_rate',
        'daily_rate',
        'hourly_rate',
        'minute_rate',
        'total_scheduled_days',
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
        'regular_pay',
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
        'employee_biometric_id' => 'integer',
        'employee_id' => 'integer',
        'payroll_employee_salary_id' => 'integer',
        'monthly_rate' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:4',
        'minute_rate' => 'decimal:4',
        'regular_pay' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'late_deduction' => 'decimal:2',
        'undertime_deduction' => 'decimal:2',
        'absence_deduction' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'holiday_pay' => 'decimal:2',
        'rest_day_pay' => 'decimal:2',
        'leave_pay' => 'decimal:2',
        'taxable_compensation' => 'decimal:2',
        'sss_employee' => 'decimal:2',
        'sss_employer' => 'decimal:2',
        'philhealth_employee' => 'decimal:2',
        'philhealth_employer' => 'decimal:2',
        'pagibig_employee' => 'decimal:2',
        'pagibig_employer' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'total_employee_government_deductions' => 'decimal:2',
        'total_employer_government_contributions' => 'decimal:2',
        'other_additions' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'meta' => 'array',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employeeBiometric(): BelongsTo
    {
        return $this->belongsTo(EmployeeBiometric::class, 'employee_biometric_id');
    }

    public function salaryProfile(): BelongsTo
    {
        return $this->belongsTo(PayrollEmployeeSalary::class, 'payroll_employee_salary_id');
    }

    public function paymentLogs(): HasMany
    {
        return $this->hasMany(PaymentLog::class, 'payroll_item_id');
    }
}
