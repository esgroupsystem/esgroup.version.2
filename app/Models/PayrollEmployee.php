<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function attendanceSummaries()
    {
        return $this->hasMany(AttendanceDailySummary::class);
    }

    public function payrollEntries()
    {
        return $this->hasMany(PayrollEntry::class);
    }
}
