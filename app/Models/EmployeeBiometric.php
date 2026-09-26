<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\PayrollEmployeeNameFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string|null $employment_status
 * @property bool|null $is_payroll_active
 * @property int|string|null $group_name
 * @property string|null $display_employee_no
 * @property string|null $display_name
 * @property string|null $source_employee_id
 * @property string|null $source_employee_no
 * @property string|null $source_employee_name
 * @property string|null $source_crosschex_id
 * @property string|null $source_crosschex_account_name
 * @property string|null $source_crosschex_account
 * @property string|null $last_check_time
 * @property int $total_logs
 * @property-read PayrollEmployeeSalary|null $activeSalaryProfile

 * @property string|null $source_key
 * @property string|null $employee_identity_hash
 * @property int|null $biometric_company_id
 * @property \Carbon\CarbonInterface|null $inactive_at
 * @property string|null $device_sn
 * @property string|null $device_name
 * @property string|null $remarks
 * @property-read mixed $effective_employee_no
 * @property-read mixed $effective_name
 * @property-read mixed $payroll_display_name
 * @property-read mixed $legacy_biometric_employee_id
 * @property-read mixed $payroll_group_label
 */
class EmployeeBiometric extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const PAYROLL_GROUP_MIRASOL = 1;

    public const PAYROLL_GROUP_GONZALES = 2;

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
        'group_name' => 'integer',
        'is_payroll_active' => 'boolean',
        'inactive_at' => 'datetime',
        'last_check_time' => 'datetime',
        'total_logs' => 'integer',
    ];

    /** The HR employee (201 file) manually linked to this record. */
    /** @return HasOne<Employee, $this> */
    public function hrEmployee(): HasOne
    {
        return $this->hasOne(Employee::class, 'employee_biometric_id');
    }

    /** @return BelongsTo<BiometricCompany, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(BiometricCompany::class, 'biometric_company_id');
    }

    /** @return HasMany<DailyAttendanceSummary, $this> */
    public function attendanceSummaries(): HasMany
    {
        return $this->hasMany(DailyAttendanceSummary::class, 'employee_biometric_id');
    }

    /** @return HasMany<PayrollAttendanceAdjustment, $this> */
    public function attendanceAdjustments(): HasMany
    {
        return $this->hasMany(PayrollAttendanceAdjustment::class, 'employee_biometric_id');
    }

    /** @return HasMany<EmployeePlottingSchedule, $this> */
    public function plottingSchedules(): HasMany
    {
        return $this->hasMany(EmployeePlottingSchedule::class, 'employee_biometric_id');
    }

    /** @return HasMany<PayrollEmployeeSalary, $this> */
    public function salaryProfiles(): HasMany
    {
        return $this->hasMany(PayrollEmployeeSalary::class, 'employee_biometric_id');
    }

    /** @return HasOne<PayrollEmployeeSalary, $this> */
    public function activeSalaryProfile(): HasOne
    {
        return $this->hasOne(PayrollEmployeeSalary::class, 'employee_biometric_id')
            ->where('is_active', true)
            ->latestOfMany();
    }

    /** @return HasMany<BenefitContributionRecord, $this> */
    public function benefitContributionRecords(): HasMany
    {
        return $this->hasMany(BenefitContributionRecord::class, 'employee_biometric_id');
    }

    /** @return HasMany<PayrollItem, $this> */
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
            ->where(function (Builder $query): void {
                $query->where('is_payroll_active', true)
                    ->orWhereNull('is_payroll_active');
            })
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

    /**
     * Payroll/Biometrics display name only. Source names stay unchanged so
     * employee identity matching remains backward compatible.
     */
    public function getPayrollDisplayNameAttribute(): string
    {
        return PayrollEmployeeNameFormatter::display($this->effective_name);
    }

    /**
     * Directory order requested by Payroll/HR:
     * Active employees first, then Inactive, A-Z by surname inside each group.
     */
    public function scopePayrollDirectoryOrder(Builder $query): Builder
    {
        $query->orderByRaw(
            "CASE
                WHEN employment_status = 'inactive' OR is_payroll_active = 0 THEN 1
                ELSE 0
            END ASC"
        );

        $nameExpression = "COALESCE(
            NULLIF(TRIM(display_name), ''),
            NULLIF(TRIM(source_employee_name), ''),
            NULLIF(TRIM(source_crosschex_account_name), ''),
            NULLIF(TRIM(source_crosschex_account), ''),
            'Unknown Employee'
        )";

        // Production is MySQL/MariaDB. Keep SQLite-compatible fallback for tests.
        if (config('database.default') === 'sqlite') {
            return $query
                ->orderByRaw("LOWER({$nameExpression}) ASC")
                ->orderBy('id');
        }

        return $query
            ->orderByRaw(
                "LOWER(
                    CASE
                        WHEN {$nameExpression} LIKE '%,%'
                            THEN TRIM(SUBSTRING_INDEX({$nameExpression}, ',', 1))
                        ELSE SUBSTRING_INDEX(TRIM({$nameExpression}), ' ', -1)
                    END
                ) ASC"
            )
            ->orderByRaw("LOWER({$nameExpression}) ASC")
            ->orderBy('id');
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

    public function getPayrollGroupLabelAttribute(): string
    {
        return match ($this->group_name) {
            self::PAYROLL_GROUP_MIRASOL => 'Mirasol / Balintawak Payroll',
            self::PAYROLL_GROUP_GONZALES => 'Gonzales Payroll',
            default => 'No Payroll Group',
        };
    }
}
