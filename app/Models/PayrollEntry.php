<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $payroll_period_id
 * @property string|null $payroll_employee_id
 * @property string|null $days_worked
 * @property string|null $worked_minutes
 * @property string|null $late_minutes
 * @property string|null $undertime_minutes
 * @property string|null $overtime_minutes
 * @property string|float|int $basic_pay
 * @property string|float|int $overtime_pay
 * @property string|float|int $allowances
 * @property string|float|int $gross_pay
 * @property string|float|int $sss
 * @property string|float|int $philhealth
 * @property string|float|int $pagibig
 * @property string|float|int $withholding_tax
 * @property string|float|int $other_deductions
 * @property string|float|int $net_pay
 * @property string|null $status
 */
class PayrollEntry extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'payroll_employee_id',
        'days_worked',
        'worked_minutes',
        'late_minutes',
        'undertime_minutes',
        'overtime_minutes',
        'basic_pay',
        'overtime_pay',
        'allowances',
        'gross_pay',
        'sss',
        'philhealth',
        'pagibig',
        'withholding_tax',
        'other_deductions',
        'net_pay',
        'status',
    ];

    protected $casts = [
        'basic_pay' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'allowances' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'sss' => 'decimal:2',
        'philhealth' => 'decimal:2',
        'pagibig' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    /** @return BelongsTo<PayrollEmployee, $this> */
    public function payrollEmployee(): BelongsTo
    {
        return $this->belongsTo(PayrollEmployee::class);
    }

    /** @return BelongsTo<PayrollPeriod, $this> */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    /** @return HasMany<PayrollAdjustment, $this> */
    public function adjustments(): HasMany
    {
        return $this->hasMany(PayrollAdjustment::class);
    }
}
