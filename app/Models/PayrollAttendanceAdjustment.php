<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAttendanceAdjustment extends Model
{
    public const TYPE_SICK_LEAVE = 'sick_leave';

    public const TYPE_MEDICAL_LEAVE = 'medical_leave';

    public const TYPE_CHANGE_SCHEDULE = 'change_schedule';

    public const TYPE_OFFSET = 'offset';

    public const TYPE_OFFICIAL_BUSINESS = 'official_business';

    public const TYPE_HOLIDAY_WORK = 'holiday_work';

    public const TYPE_OVERTIME = 'overtime';

    public const TYPE_TYPHOON_DISASTER = 'typhoon_disaster';

    public const GLOBAL_DISASTER_BIOMETRIC_ID = 'GLOBAL-DISASTER';

    public const GLOBAL_DISASTER_EMPLOYEE_NAME = 'ALL EMPLOYEES';

    public const TYPES = [
        self::TYPE_SICK_LEAVE => 'Sick Leave',
        self::TYPE_MEDICAL_LEAVE => 'Medical Leave',
        self::TYPE_CHANGE_SCHEDULE => 'Change Schedule',
        self::TYPE_OFFSET => 'Offset',
        self::TYPE_OFFICIAL_BUSINESS => 'Official Business',
        self::TYPE_HOLIDAY_WORK => 'Holiday Work',
        self::TYPE_OVERTIME => 'Overtime',
        self::TYPE_TYPHOON_DISASTER => 'Typhoon / Disaster - All Employees',
    ];

    protected $fillable = [
        'employee_biometric_id',
        'biometric_employee_id',
        'employee_no',
        'employee_name',
        'crosschex_id',
        'work_date',
        'date_from',
        'date_to',
        'adjustment_type',
        'adjusted_time_in',
        'adjusted_time_out',
        'adjusted_day_type',
        'offset_source_date',
        'offset_source_time_in',
        'offset_source_time_out',
        'offset_source_logs',
        'is_paid',
        'ignore_late',
        'ignore_undertime',
        'reason',
        'remarks',
        'status',
        'encoded_by',
        'encoded_at',
    ];

    protected function casts(): array
    {
        return [
            'employee_biometric_id' => 'integer',
            'work_date' => 'date',
            'date_from' => 'date',
            'date_to' => 'date',
            'offset_source_date' => 'date',
            'offset_source_logs' => 'array',
            'is_paid' => 'boolean',
            'ignore_late' => 'boolean',
            'ignore_undertime' => 'boolean',
            'encoded_at' => 'datetime',
        ];
    }

    public function employeeBiometric(): BelongsTo
    {
        return $this->belongsTo(EmployeeBiometric::class, 'employee_biometric_id');
    }

    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('status')
                ->orWhere('status', 'approved');
        });
    }

    public function scopeForPayrollActiveEmployees(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('adjustment_type', self::TYPE_TYPHOON_DISASTER)
                ->orWhereHas('employeeBiometric', function (Builder $query): void {
                    $query->payrollActive();
                });
        });
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->adjustment_type] ?? ucwords(str_replace('_', ' ', $this->adjustment_type));
    }

    public function getPeriodLabelAttribute(): string
    {
        if ($this->date_from && $this->date_to) {
            if ($this->date_from->isSameDay($this->date_to)) {
                return $this->date_from->format('M d, Y');
            }

            return $this->date_from->format('M d, Y').' - '.$this->date_to->format('M d, Y');
        }

        return $this->work_date?->format('M d, Y') ?? '—';
    }

    public function getAdjustedTimeLabelAttribute(): string
    {
        if ($this->adjustment_type === self::TYPE_TYPHOON_DISASTER) {
            return 'Whole day paid for employees with time-in';
        }

        if (! $this->adjusted_time_in && ! $this->adjusted_time_out) {
            return 'No manual time';
        }

        return ($this->adjusted_time_in ?? '--:--').' - '.($this->adjusted_time_out ?? '--:--');
    }

    public function getOffsetProofLabelAttribute(): string
    {
        if ($this->adjustment_type !== self::TYPE_OFFSET) {
            return 'Not applicable';
        }

        if (! $this->offset_source_date) {
            return 'No proof date';
        }

        return $this->offset_source_date->format('M d, Y').' | '
            .($this->offset_source_time_in ?? '--:--').' - '
            .($this->offset_source_time_out ?? '--:--');
    }

    public function isGlobalDisasterAdjustment(): bool
    {
        return $this->adjustment_type === self::TYPE_TYPHOON_DISASTER;
    }
}
