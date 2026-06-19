<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    protected $fillable = [
        'payroll_id',
        'payroll_item_id',
        'employee_id',
        'payroll_employee_salary_id',
        'biometric_employee_id',
        'employee_no',
        'employee_name',
        'log_type',
        'source_type',
        'source_id',
        'source_name',
        'deduction_schedule',
        'cutoff_month',
        'cutoff_year',
        'cutoff_type',
        'contribution_month',
        'contribution_year',
        'period_start',
        'period_end',
        'amount',
        'employee_share',
        'employer_share',
        'balance_before',
        'balance_after',
        'payment_no',
        'remaining_payments',
        'reference',
        'remarks',
        'posted_at',
        'created_by',
        'meta',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'posted_at' => 'datetime',
        'amount' => 'decimal:2',
        'employee_share' => 'decimal:2',
        'employer_share' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
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
}
