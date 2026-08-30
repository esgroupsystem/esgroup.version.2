<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $payroll_employee_salary_id
 * @property string|null $name
 * @property string|float|int $total_amount
 * @property string|float|int $payment_amount
 * @property string|null $deduction_schedule
 * @property \Carbon\CarbonInterface|null $start_date
 * @property string|null $remarks
 * @property bool|null $is_active
 */
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

    /** @return BelongsTo<PayrollEmployeeSalary, $this> */
    public function salary(): BelongsTo
    {
        return $this->belongsTo(PayrollEmployeeSalary::class, 'payroll_employee_salary_id');
    }
}
