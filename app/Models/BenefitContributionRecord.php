<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenefitContributionRecord extends Model
{
    protected $fillable = [
        'monthly_key',
        'payroll_id',
        'payroll_item_id',
        'employee_biometric_id',
        'employee_id',
        'payroll_employee_salary_id',
        'posted_by',
        'garage_group',
        'contribution_month',
        'contribution_year',
        'period_start',
        'period_end',
        'payroll_number',
        'employee_no',
        'employee_name',
        'company_name',
        'sss_number',
        'philhealth_number',
        'pagibig_number',
        'gross_compensation',
        'business_first_cutoff_gross',
        'business_second_cutoff_gross',
        'monthly_basic_salary',
        'sss_compensation_basis',
        'sss_compensation_range_minimum',
        'sss_compensation_range_maximum',
        'sss_msc',
        'sss_regular_ss_msc',
        'sss_mpf_msc',
        'sss_employee_regular_ss',
        'sss_employee_mpf',
        'sss_employee_total',
        'sss_employer_regular_ss',
        'sss_employer_mpf',
        'sss_employer_ec',
        'sss_employer_total',
        'sss_total_contribution',
        'philhealth_basis',
        'philhealth_salary_base',
        'philhealth_premium_rate',
        'philhealth_employee',
        'philhealth_employer',
        'philhealth_total',
        'pagibig_basis',
        'pagibig_fund_salary',
        'pagibig_employee_rate',
        'pagibig_employer_rate',
        'pagibig_employee',
        'pagibig_employer',
        'pagibig_total',
        'employee_total',
        'employer_total',
        'grand_total',
        'posted_at',
        'meta',
    ];

    protected $casts = [
        'payroll_id' => 'integer',
        'payroll_item_id' => 'integer',
        'employee_biometric_id' => 'integer',
        'employee_id' => 'integer',
        'payroll_employee_salary_id' => 'integer',
        'posted_by' => 'integer',
        'garage_group' => 'integer',
        'contribution_month' => 'integer',
        'contribution_year' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
        'gross_compensation' => 'decimal:2',
        'business_first_cutoff_gross' => 'decimal:2',
        'business_second_cutoff_gross' => 'decimal:2',
        'monthly_basic_salary' => 'decimal:2',
        'sss_compensation_basis' => 'decimal:2',
        'sss_compensation_range_minimum' => 'decimal:2',
        'sss_compensation_range_maximum' => 'decimal:2',
        'sss_msc' => 'decimal:2',
        'sss_regular_ss_msc' => 'decimal:2',
        'sss_mpf_msc' => 'decimal:2',
        'sss_employee_regular_ss' => 'decimal:2',
        'sss_employee_mpf' => 'decimal:2',
        'sss_employee_total' => 'decimal:2',
        'sss_employer_regular_ss' => 'decimal:2',
        'sss_employer_mpf' => 'decimal:2',
        'sss_employer_ec' => 'decimal:2',
        'sss_employer_total' => 'decimal:2',
        'sss_total_contribution' => 'decimal:2',
        'philhealth_basis' => 'decimal:2',
        'philhealth_salary_base' => 'decimal:2',
        'philhealth_premium_rate' => 'decimal:6',
        'philhealth_employee' => 'decimal:2',
        'philhealth_employer' => 'decimal:2',
        'philhealth_total' => 'decimal:2',
        'pagibig_basis' => 'decimal:2',
        'pagibig_fund_salary' => 'decimal:2',
        'pagibig_employee_rate' => 'decimal:6',
        'pagibig_employer_rate' => 'decimal:6',
        'pagibig_employee' => 'decimal:2',
        'pagibig_employer' => 'decimal:2',
        'pagibig_total' => 'decimal:2',
        'employee_total' => 'decimal:2',
        'employer_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'posted_at' => 'datetime',
        'meta' => 'array',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }

    public function employeeBiometric(): BelongsTo
    {
        return $this->belongsTo(EmployeeBiometric::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryProfile(): BelongsTo
    {
        return $this->belongsTo(PayrollEmployeeSalary::class, 'payroll_employee_salary_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
