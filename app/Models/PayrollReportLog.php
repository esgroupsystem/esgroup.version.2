<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollReportLog extends Model
{
    protected $fillable = [
        'payroll_id',
        'payroll_item_id',
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

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }
}
