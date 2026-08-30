<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $employee_id
 * @property string|null $source
 * @property string|null $crosschex_id
 * @property string|null $employee_no
 * @property string|null $employee_name
 * @property string|null $department
 * @property string|null $position
 * @property string|float|int $daily_rate
 * @property string|float|int $monthly_rate
 * @property string|float|int $hourly_rate
 * @property bool|null $is_active
 */
class PayrollEmployee extends Model
{
    protected $fillable = [
        'employee_id',
        'source',
        'crosschex_id',
        'employee_no',
        'employee_name',
        'department',
        'position',
        'daily_rate',
        'monthly_rate',
        'hourly_rate',
        'is_active',
    ];

    protected $casts = [
        'daily_rate' => 'decimal:2',
        'monthly_rate' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /** @return HasMany<AttendanceDailySummary, $this> */
    public function attendanceSummaries(): HasMany
    {
        return $this->hasMany(AttendanceDailySummary::class);
    }

    /** @return HasMany<PayrollEntry, $this> */
    public function payrollEntries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }
}
