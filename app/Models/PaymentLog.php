<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $payroll_id
 * @property string|null $payroll_item_id
 * @property string|null $employee_id
 * @property string|null $payroll_employee_salary_id
 * @property string|null $biometric_employee_id
 * @property string|null $employee_no
 * @property string|null $employee_name
 * @property string|null $log_type
 * @property string|null $source_type
 * @property string|null $source_id
 * @property string|null $source_name
 * @property string|null $deduction_schedule
 * @property string|null $cutoff_month
 * @property string|null $cutoff_year
 * @property string|null $cutoff_type
 * @property string|null $contribution_month
 * @property string|null $contribution_year
 * @property \Carbon\CarbonInterface|null $period_start
 * @property \Carbon\CarbonInterface|null $period_end
 * @property string|float|int $amount
 * @property string|float|int $employee_share
 * @property string|float|int $employer_share
 * @property string|float|int $balance_before
 * @property string|float|int $balance_after
 * @property string|null $payment_no
 * @property string|null $remaining_payments
 * @property string|null $reference
 * @property string|null $remarks
 * @property \Carbon\CarbonInterface|null $posted_at
 * @property string|null $created_by
 * @property array<string, mixed>|null $meta
 */
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

    /** @return BelongsTo<Payroll, $this> */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    /** @return BelongsTo<PayrollItem, $this> */
    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }
}
