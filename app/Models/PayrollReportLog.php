<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $payroll_id
 * @property string|null $payroll_item_id
 * @property int|null $employee_biometric_id
 * @property string|null $employee_id
 * @property string|null $biometric_employee_id
 * @property string|null $employee_no
 * @property string|null $employee_name
 * @property string|null $report_type
 * @property string|null $cutoff_month
 * @property string|null $cutoff_year
 * @property string|null $cutoff_type
 * @property string|null $contribution_month
 * @property string|null $contribution_year
 * @property \Carbon\CarbonInterface|null $period_start
 * @property \Carbon\CarbonInterface|null $period_end
 * @property string|float|int $basis_amount
 * @property string|float|int $computed_amount
 * @property string|null $status
 * @property string|null $remarks
 * @property \Carbon\CarbonInterface|null $generated_at
 * @property string|null $generated_by
 * @property array<string, mixed>|null $meta
 */
class PayrollReportLog extends Model
{
    protected $fillable = [
        'payroll_id',
        'payroll_item_id',
        'employee_biometric_id',
        'employee_id',
        'biometric_employee_id',
        'employee_no',
        'employee_name',
        'report_type',
        'cutoff_month',
        'cutoff_year',
        'cutoff_type',
        'contribution_month',
        'contribution_year',
        'period_start',
        'period_end',
        'basis_amount',
        'computed_amount',
        'status',
        'remarks',
        'generated_at',
        'generated_by',
        'meta',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'datetime',
        'basis_amount' => 'decimal:2',
        'computed_amount' => 'decimal:2',
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
