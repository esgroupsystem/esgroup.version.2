<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollEmployeeSalary extends Model
{
    protected $fillable = [
        'employee_biometric_id',
        'employee_id',
        'biometric_employee_id',
        'employee_no',
        'employee_name',
        'crosschex_id',

        'rate_type',
        'basic_salary',
        'allowance',
        'allowance_release_schedule',
        'sim_load_allowance',
        'sim_load_release_schedule',

        'sss_contribution_cutoff',
        'pagibig_contribution_cutoff',
        'philhealth_contribution_cutoff',

        'ot_rate_per_hour',
        'late_deduction_per_minute',
        'undertime_deduction_per_minute',
        'absent_deduction_per_day',

        'sss_loan',
        'pagibig_loan',
        'vale',
        'other_loans',

        'sss_loan_total_amount',
        'sss_loan_payment_amount',
        'sss_loan_deduction_schedule',
        'sss_loan_start_date',

        'pagibig_loan_total_amount',
        'pagibig_loan_payment_amount',
        'pagibig_loan_deduction_schedule',
        'pagibig_loan_start_date',

        'philhealth_loan_total_amount',
        'philhealth_loan_payment_amount',
        'philhealth_loan_deduction_schedule',
        'philhealth_loan_start_date',

        'cash_advance_total_amount',
        'cash_advance_payment_amount',
        'cash_advance_deduction_schedule',
        'cash_advance_start_date',

        'other_loan_total_amount',
        'other_loan_payment_amount',
        'other_loan_deduction_schedule',
        'other_loan_start_date',

        'is_active',
        'remarks',
    ];

    protected $casts = [
        'employee_biometric_id' => 'integer',
        'employee_id' => 'integer',

        'basic_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'sim_load_allowance' => 'decimal:2',

        'ot_rate_per_hour' => 'decimal:2',
        'late_deduction_per_minute' => 'decimal:4',
        'undertime_deduction_per_minute' => 'decimal:4',
        'absent_deduction_per_day' => 'decimal:2',

        'sss_loan' => 'decimal:2',
        'pagibig_loan' => 'decimal:2',
        'vale' => 'decimal:2',
        'other_loans' => 'decimal:2',

        'sss_loan_total_amount' => 'decimal:2',
        'sss_loan_payment_amount' => 'decimal:2',
        'sss_loan_start_date' => 'date',

        'pagibig_loan_total_amount' => 'decimal:2',
        'pagibig_loan_payment_amount' => 'decimal:2',
        'pagibig_loan_start_date' => 'date',

        'philhealth_loan_total_amount' => 'decimal:2',
        'philhealth_loan_payment_amount' => 'decimal:2',
        'philhealth_loan_start_date' => 'date',

        'cash_advance_total_amount' => 'decimal:2',
        'cash_advance_payment_amount' => 'decimal:2',
        'cash_advance_start_date' => 'date',

        'other_loan_total_amount' => 'decimal:2',
        'other_loan_payment_amount' => 'decimal:2',
        'other_loan_start_date' => 'date',

        'is_active' => 'boolean',
    ];

    public function employeeBiometric(): BelongsTo
    {
        return $this->belongsTo(EmployeeBiometric::class, 'employee_biometric_id');
    }

    public function otherDeductions(): HasMany
    {
        return $this->hasMany(PayrollEmployeeSalaryOtherDeduction::class, 'payroll_employee_salary_id')
            ->orderBy('name');
    }

    public function activeOtherDeductions(): HasMany
    {
        return $this->hasMany(PayrollEmployeeSalaryOtherDeduction::class, 'payroll_employee_salary_id')
            ->where('is_active', true)
            ->orderBy('name');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForPayrollActiveEmployees(Builder $query): Builder
    {
        return $query->whereHas('employeeBiometric', function (Builder $query): void {
            $query->payrollActive();
        });
    }
}
