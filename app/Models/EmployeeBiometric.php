<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeBiometric extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'source_key',
        'employee_identity_hash',
        'biometric_company_id',
        'display_employee_no',
        'display_name',
        'employment_status',
        'group_name',
        'is_payroll_active',
        'inactive_at',
        'source_crosschex_account',
        'source_crosschex_account_name',
        'source_crosschex_id',
        'source_employee_id',
        'source_employee_no',
        'source_employee_name',
        'device_sn',
        'device_name',
        'last_check_time',
        'total_logs',
        'remarks',
    ];

    protected $casts = [
        'biometric_company_id' => 'integer',
        'is_payroll_active' => 'boolean',
        'inactive_at' => 'datetime',
        'last_check_time' => 'datetime',
        'total_logs' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(BiometricCompany::class, 'biometric_company_id');
    }

    public function attendanceSummaries(): HasMany
    {
        return $this->hasMany(DailyAttendanceSummary::class, 'employee_biometric_id');
    }

    public function attendanceAdjustments(): HasMany
    {
        return $this->hasMany(PayrollAttendanceAdjustment::class, 'employee_biometric_id');
    }

    public function plottingSchedules(): HasMany
    {
        return $this->hasMany(EmployeePlottingSchedule::class, 'employee_biometric_id');
    }

    public function salaryProfiles(): HasMany
    {
        return $this->hasMany(PayrollEmployeeSalary::class, 'employee_biometric_id');
    }

    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class, 'employee_biometric_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('employment_status')
                ->orWhere('employment_status', self::STATUS_ACTIVE);
        });
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('employment_status', self::STATUS_INACTIVE)
                ->orWhere('is_payroll_active', false);
        });
    }

    public function scopePayrollActive(Builder $query): Builder
    {
        return $query
            ->where('is_payroll_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('employment_status')
                    ->orWhere('employment_status', self::STATUS_ACTIVE);
            });
    }

    public function scopeGroup(Builder $query, ?string $groupName): Builder
    {
        $groupName = trim((string) $groupName);

        if ($groupName === '') {
            return $query;
        }

        return $query->where('group_name', $groupName);
    }

    public function getEffectiveEmployeeNoAttribute(): ?string
    {
        return $this->firstFilled([
            $this->display_employee_no,
            $this->source_employee_no,
            $this->source_employee_id,
            $this->source_crosschex_id,
            $this->source_key,
        ]);
    }

    public function getEffectiveNameAttribute(): string
    {
        return $this->firstFilled([
            $this->display_name,
            $this->source_employee_name,
            $this->source_crosschex_account_name,
            $this->source_crosschex_account,
        ]) ?? 'Unknown Employee';
    }

    public function getLegacyBiometricEmployeeIdAttribute(): ?string
    {
        return $this->firstFilled([
            $this->source_employee_id,
            $this->source_crosschex_id,
            $this->source_employee_no,
            $this->display_employee_no,
            $this->source_key,
        ]);
    }

    public function markPayrollInactive(?string $remarks = null): void
    {
        $this->forceFill([
            'employment_status' => self::STATUS_INACTIVE,
            'is_payroll_active' => false,
            'inactive_at' => now('Asia/Manila'),
            'remarks' => $remarks ?: $this->remarks,
        ])->save();
    }

    public function markPayrollActive(): void
    {
        $this->forceFill([
            'employment_status' => self::STATUS_ACTIVE,
            'is_payroll_active' => true,
            'inactive_at' => null,
        ])->save();
    }

    private function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            $value = trim((string) ($value ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
