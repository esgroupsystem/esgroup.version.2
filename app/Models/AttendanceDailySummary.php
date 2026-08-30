<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $payroll_employee_id
 * @property \Carbon\CarbonInterface|null $work_date
 * @property \Carbon\CarbonInterface|null $time_in
 * @property \Carbon\CarbonInterface|null $time_out
 * @property string|null $worked_minutes
 * @property string|null $late_minutes
 * @property string|null $undertime_minutes
 * @property string|null $overtime_minutes
 * @property string|null $status
 * @property string|null $remarks
 */
class AttendanceDailySummary extends Model
{
    protected $fillable = [
        'payroll_employee_id',
        'work_date',
        'time_in',
        'time_out',
        'worked_minutes',
        'late_minutes',
        'undertime_minutes',
        'overtime_minutes',
        'status',
        'remarks',
    ];

    protected $casts = [
        'work_date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    /** @return BelongsTo<PayrollEmployee, $this> */
    public function payrollEmployee(): BelongsTo
    {
        return $this->belongsTo(PayrollEmployee::class);
    }
}
