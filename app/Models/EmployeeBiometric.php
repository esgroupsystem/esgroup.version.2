<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBiometric extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'biometric_company_id',
        'source_key',
        'source_crosschex_account',
        'source_crosschex_account_name',
        'source_crosschex_id',
        'source_employee_id',
        'source_employee_no',
        'source_employee_name',
        'display_employee_no',
        'display_name',
        'device_sn',
        'device_name',
        'last_check_time',
        'total_logs',
        'employment_status',
        'remarks',
    ];

    protected $casts = [
        'last_check_time' => 'datetime',
        'total_logs' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(BiometricCompany::class, 'biometric_company_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('employment_status', self::STATUS_ACTIVE);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('employment_status', self::STATUS_INACTIVE);
    }
}
