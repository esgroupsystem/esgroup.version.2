<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollEmployeeSalary extends Model
{
    protected $fillable = [
        'biometric_employee_id',
        'employee_no',
        'employee_name',
        'crosschex_id',
        'rate_type',
        'basic_salary',
        'allowance',
        'ot_rate_per_hour',
        'late_deduction_per_minute',
        'undertime_deduction_per_minute',
        'absent_deduction_per_day',
        'is_active',
        'remarks',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'ot_rate_per_hour' => 'decimal:2',
        'late_deduction_per_minute' => 'decimal:4',
        'undertime_deduction_per_minute' => 'decimal:4',
        'absent_deduction_per_day' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
