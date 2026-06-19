<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEmployeeSalaryOtherDeduction extends Model
{
    protected $fillable = [
        'payroll_employee_salary_id',
        'name',
        'total_amount',
        'payment_amount',
        'deduction_schedule',
        'start_date',
        'remarks',
        'is_active',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'start_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function salary(): BelongsTo
    {
        return $this->belongsTo(PayrollEmployeeSalary::class, 'payroll_employee_salary_id');
    }
}
