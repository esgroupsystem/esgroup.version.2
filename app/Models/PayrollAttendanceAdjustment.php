<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\PayrollEmployeeNameFormatter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int|null $employee_biometric_id
 * @property int|null $employee_id
 * @property string|null $employee_no
 * @property string|null $employee_name
 * @property string $adjustment_type
 * @property \Carbon\CarbonInterface|null $work_date
 * @property \Carbon\CarbonInterface|null $date_from
 * @property \Carbon\CarbonInterface|null $date_to
 * @property \Carbon\CarbonInterface|null $offset_source_date
 * @property \Carbon\CarbonInterface|null $payroll_effective_date
 * @property int|null $approved_minutes
 * @property string|float|int|null $amount
 * @property bool $defer_to_next_payroll
 * @property bool $is_paid
 * @property bool $ignore_late
 * @property bool $ignore_undertime
 * @property string|null $status
 * @property string|null $adjusted_time_in
 * @property string|null $adjusted_time_out
 * @property string|null $offset_source_time_in
 * @property string|null $offset_source_time_out
 * @property-read string $type_label
 * @property-read string $status_label
 * @property-read string $period_label
 * @property-read string $adjusted_time_label
 * @property-read string $offset_proof_label

 * @property string|null $biometric_employee_id
 * @property string|null $crosschex_id
 * @property string|null $adjusted_day_type
 * @property array<string, mixed>|null $offset_source_logs
 * @property array<int, array{date: string, minutes: int, time_in: ?string, time_out: ?string}>|null $offset_sources
 * @property int|null $paid_payroll_id
 * @property int|null $paid_payroll_item_id
 * @property string|null $reason
 * @property string|null $remarks
 * @property string|null $approved_by
 * @property \Carbon\CarbonInterface|null $approved_at
 * @property string|null $rejected_by
 * @property \Carbon\CarbonInterface|null $rejected_at
 * @property string|null $rejection_reason
 * @property string|null $encoded_by
 * @property \Carbon\CarbonInterface|null $encoded_at
 * @property-read mixed $payroll_display_name
 */
class PayrollAttendanceAdjustment extends Model
{
    use SoftDeletes;

    public const TYPE_SICK_LEAVE = 'sick_leave';

    public const TYPE_MEDICAL_LEAVE = 'medical_leave';

    public const TYPE_CHANGE_SCHEDULE = 'change_schedule';

    public const TYPE_OFFSET = 'offset';

    public const TYPE_OFFICIAL_BUSINESS = 'official_business';

    public const TYPE_HOLIDAY_WORK = 'holiday_work';

    public const TYPE_OVERTIME = 'overtime';

    public const TYPE_CASH_ADJUSTMENT = 'cash_adjustment';

    /**
     * Legacy generic type. New records must use one of the threshold-specific
     * variants below. It is still recognized as a 3-hour rule so old data can
     * be rebuilt safely before/after the data migration.
     */
    public const TYPE_TYPHOON_DISASTER = 'typhoon_disaster';

    public const TYPE_TYPHOON_DISASTER_3H = 'typhoon_disaster_3h';

    public const TYPE_TYPHOON_DISASTER_4H = 'typhoon_disaster_4h';

    public const TYPE_TYPHOON_DISASTER_5H = 'typhoon_disaster_5h';

    public const TYPE_TYPHOON_DISASTER_6H = 'typhoon_disaster_6h';

    public const TYPHOON_DISASTER_TYPES = [
        self::TYPE_TYPHOON_DISASTER,
        self::TYPE_TYPHOON_DISASTER_3H,
        self::TYPE_TYPHOON_DISASTER_4H,
        self::TYPE_TYPHOON_DISASTER_5H,
        self::TYPE_TYPHOON_DISASTER_6H,
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const GLOBAL_DISASTER_BIOMETRIC_ID = 'GLOBAL-DISASTER';

    public const GLOBAL_DISASTER_EMPLOYEE_NAME = 'ALL EMPLOYEES';

    public const TYPES = [
        self::TYPE_SICK_LEAVE => 'Sick Leave',
        self::TYPE_MEDICAL_LEAVE => 'Medical Leave',
        self::TYPE_CHANGE_SCHEDULE => 'Change Schedule',
        self::TYPE_OFFSET => 'Offset / Company Compensatory Leave',
        self::TYPE_OFFICIAL_BUSINESS => 'Official Business',
        self::TYPE_HOLIDAY_WORK => 'Holiday Work',
        self::TYPE_OVERTIME => 'Overtime - Manager Approval Required',
        self::TYPE_CASH_ADJUSTMENT => 'Salary Adjustment',
        self::TYPE_TYPHOON_DISASTER_3H => 'Typhoon / Disaster - All Employees - 3hrs',
        self::TYPE_TYPHOON_DISASTER_4H => 'Typhoon / Disaster - All Employees - 4hrs',
        self::TYPE_TYPHOON_DISASTER_5H => 'Typhoon / Disaster - All Employees - 5hrs',
        self::TYPE_TYPHOON_DISASTER_6H => 'Typhoon / Disaster - All Employees - 6hrs',
    ];

    /**
     * Central definition of how each adjustment behaves.
     * Attendance and payroll services use these semantics instead of treating
     * every adjustment as a generic paid manual-time record.
     */
    public const TYPE_RULES = [
        self::TYPE_SICK_LEAVE => [
            'date_mode' => 'range',
            'manual_time_mode' => 'none',
            'default_paid' => true,
            'default_ignore_late' => true,
            'default_ignore_undertime' => true,
            'approval_required' => false,
        ],
        self::TYPE_MEDICAL_LEAVE => [
            'date_mode' => 'range',
            'manual_time_mode' => 'none',
            'default_paid' => true,
            'default_ignore_late' => true,
            'default_ignore_undertime' => true,
            'approval_required' => false,
        ],
        self::TYPE_CHANGE_SCHEDULE => [
            'date_mode' => 'single',
            'manual_time_mode' => 'schedule',
            'default_paid' => false,
            'default_ignore_late' => false,
            'default_ignore_undertime' => false,
            'approval_required' => false,
        ],
        self::TYPE_OFFSET => [
            'date_mode' => 'single',
            'manual_time_mode' => 'none',
            'default_paid' => false,
            'default_ignore_late' => false,
            'default_ignore_undertime' => false,
            'approval_required' => true,
            'uses_time_credit' => true,
            'defer_to_next_payroll' => false,
            'preserves_overtime_entitlement' => true,
        ],
        self::TYPE_OFFICIAL_BUSINESS => [
            'date_mode' => 'single',
            'manual_time_mode' => 'actual',
            'default_paid' => true,
            'default_ignore_late' => true,
            'default_ignore_undertime' => true,
            'approval_required' => false,
        ],
        self::TYPE_HOLIDAY_WORK => [
            'date_mode' => 'single',
            'manual_time_mode' => 'actual',
            'default_paid' => false,
            'default_ignore_late' => true,
            'default_ignore_undertime' => true,
            'approval_required' => false,
        ],
        self::TYPE_OVERTIME => [
            'date_mode' => 'single',
            'manual_time_mode' => 'overtime',
            'default_paid' => false,
            'default_ignore_late' => false,
            'default_ignore_undertime' => false,
            'approval_required' => true,
        ],
        self::TYPE_CASH_ADJUSTMENT => [
            'date_mode' => 'single',
            'manual_time_mode' => 'none',
            'default_paid' => true,
            'default_ignore_late' => false,
            'default_ignore_undertime' => false,
            'approval_required' => false,
            'uses_amount' => true,
        ],
        self::TYPE_TYPHOON_DISASTER => [
            'date_mode' => 'single',
            'manual_time_mode' => 'none',
            'default_paid' => true,
            'default_ignore_late' => true,
            'default_ignore_undertime' => true,
            'approval_required' => false,
        ],
        self::TYPE_TYPHOON_DISASTER_3H => [
            'date_mode' => 'single',
            'manual_time_mode' => 'none',
            'default_paid' => true,
            'default_ignore_late' => true,
            'default_ignore_undertime' => true,
            'approval_required' => false,
        ],
        self::TYPE_TYPHOON_DISASTER_4H => [
            'date_mode' => 'single',
            'manual_time_mode' => 'none',
            'default_paid' => true,
            'default_ignore_late' => true,
            'default_ignore_undertime' => true,
            'approval_required' => false,
        ],
        self::TYPE_TYPHOON_DISASTER_5H => [
            'date_mode' => 'single',
            'manual_time_mode' => 'none',
            'default_paid' => true,
            'default_ignore_late' => true,
            'default_ignore_undertime' => true,
            'approval_required' => false,
        ],
        self::TYPE_TYPHOON_DISASTER_6H => [
            'date_mode' => 'single',
            'manual_time_mode' => 'none',
            'default_paid' => true,
            'default_ignore_late' => true,
            'default_ignore_undertime' => true,
            'approval_required' => false,
        ],
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
        'offset_sources',
        'approved_minutes',
        'amount',
        'defer_to_next_payroll',
        'payroll_effective_date',
        'paid_payroll_id',
        'paid_payroll_item_id',
        'is_paid',
        'ignore_late',
        'ignore_undertime',
        'reason',
        'remarks',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'attachment_size',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
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
            'offset_sources' => 'array',
            'approved_minutes' => 'integer',
            'amount' => 'decimal:2',
            'defer_to_next_payroll' => 'boolean',
            'payroll_effective_date' => 'date',
            'paid_payroll_id' => 'integer',
            'paid_payroll_item_id' => 'integer',
            'is_paid' => 'boolean',
            'ignore_late' => 'boolean',
            'ignore_undertime' => 'boolean',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'encoded_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<EmployeeBiometric, $this> */
    public function employeeBiometric(): BelongsTo
    {
        return $this->belongsTo(EmployeeBiometric::class, 'employee_biometric_id');
    }

    /** @return BelongsTo<User, $this> */
    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<User, $this> */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /** @return BelongsTo<Payroll, $this> */
    public function paidPayroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class, 'paid_payroll_id');
    }

    /** @return BelongsTo<PayrollItem, $this> */
    public function paidPayrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class, 'paid_payroll_item_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('status')
                ->orWhere('status', self::STATUS_APPROVED);
        });
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForPayrollActiveEmployees(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereIn('adjustment_type', self::TYPHOON_DISASTER_TYPES)
                ->orWhereHas('employeeBiometric', function (Builder $query): void {
                    $query->where('is_payroll_active', true)
                        ->where('employment_status', 'active');
                });
        });
    }

    public static function rulesFor(string $type): array
    {
        return self::TYPE_RULES[$type] ?? [
            'date_mode' => 'single',
            'manual_time_mode' => 'none',
            'default_paid' => false,
            'default_ignore_late' => false,
            'default_ignore_undertime' => false,
            'approval_required' => false,
        ];
    }

    public static function isTyphoonDisasterType(?string $type): bool
    {
        return in_array((string) $type, self::TYPHOON_DISASTER_TYPES, true);
    }

    public static function isCashAdjustmentType(?string $type): bool
    {
        return (string) $type === self::TYPE_CASH_ADJUSTMENT;
    }

    public static function typhoonDisasterRequiredMinutes(?string $type): ?int
    {
        return match ((string) $type) {
            self::TYPE_TYPHOON_DISASTER,
            self::TYPE_TYPHOON_DISASTER_3H => 180,
            self::TYPE_TYPHOON_DISASTER_4H => 240,
            self::TYPE_TYPHOON_DISASTER_5H => 300,
            self::TYPE_TYPHOON_DISASTER_6H => 360,
            default => null,
        };
    }

    public static function typhoonDisasterRequiredHours(?string $type): ?int
    {
        $minutes = self::typhoonDisasterRequiredMinutes($type);

        return $minutes === null ? null : (int) ($minutes / 60);
    }

    public static function typeLabel(?string $type): string
    {
        if ((string) $type === self::TYPE_TYPHOON_DISASTER) {
            return 'Typhoon / Disaster - All Employees - 3hrs';
        }

        return self::TYPES[(string) $type] ?? ucwords(str_replace('_', ' ', (string) $type));
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabel($this->adjustment_type);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending Approval',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Approved',
        };
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
        if (self::isTyphoonDisasterType($this->adjustment_type)) {
            $hours = self::typhoonDisasterRequiredHours($this->adjustment_type) ?? 3;

            return "Whole day paid after {$hours} completed biometric work hour(s)";
        }

        if ($this->adjustment_type === self::TYPE_CASH_ADJUSTMENT) {
            $amount = round((float) ($this->amount ?? 0), 2);

            if ($amount == 0.0) {
                return 'No amount entered';
            }

            return $amount > 0
                ? '+₱'.number_format($amount, 2).' salary addition'
                : '-₱'.number_format(abs($amount), 2).' salary deduction';
        }

        if ($this->adjustment_type === self::TYPE_OFFSET) {
            if (! $this->approved_minutes) {
                return 'Source excess time determines credit';
            }

            $sourceCount = count($this->resolvedOffsetSources());

            return number_format($this->approved_minutes / 60, 2).' compensatory hour(s)'
                .($sourceCount > 1 ? " from {$sourceCount} source dates" : '');
        }

        if (! $this->adjusted_time_in && ! $this->adjusted_time_out) {
            return 'No manual time';
        }

        $prefix = $this->adjustment_type === self::TYPE_OVERTIME ? 'OT: ' : '';

        return $prefix.($this->adjusted_time_in ?? '--:--').' - '.($this->adjusted_time_out ?? '--:--');
    }

    public function getOffsetProofLabelAttribute(): string
    {
        if ($this->adjustment_type !== self::TYPE_OFFSET) {
            return 'Not applicable';
        }

        $sources = $this->resolvedOffsetSources();

        if ($sources === []) {
            return 'No proof date';
        }

        return collect($sources)
            ->map(fn (array $source): string => \Carbon\Carbon::parse($source['date'])->format('M d, Y').' | '
                .($source['time_in'] ?? '--:--').' - '
                .($source['time_out'] ?? '--:--')
                .(count($sources) > 1 ? ' ('.number_format($source['minutes'] / 60, 2).' hr)' : ''))
            ->implode('; ');
    }

    /**
     * Source dates whose excess time funds this Offset. Legacy records only
     * have the single offset_source_date column, which is mapped to one entry.
     *
     * @return array<int, array{date: string, minutes: int, time_in: ?string, time_out: ?string}>
     */
    public function resolvedOffsetSources(): array
    {
        if ($this->adjustment_type !== self::TYPE_OFFSET) {
            return [];
        }

        $sources = collect(is_array($this->offset_sources) ? $this->offset_sources : [])
            ->filter(fn (mixed $source): bool => is_array($source) && filled($source['date'] ?? null))
            ->map(fn (array $source): array => [
                'date' => \Carbon\Carbon::parse($source['date'])->toDateString(),
                'minutes' => max(0, (int) ($source['minutes'] ?? 0)),
                'time_in' => $source['time_in'] ?? null,
                'time_out' => $source['time_out'] ?? null,
            ])
            ->sortBy('date')
            ->values()
            ->all();

        if ($sources !== []) {
            return $sources;
        }

        if (! $this->offset_source_date) {
            return [];
        }

        return [[
            'date' => $this->offset_source_date->toDateString(),
            'minutes' => max(0, (int) ($this->approved_minutes ?? 0)),
            'time_in' => $this->offset_source_time_in,
            'time_out' => $this->offset_source_time_out,
        ]];
    }

    public function isGlobalDisasterAdjustment(): bool
    {
        return self::isTyphoonDisasterType($this->adjustment_type);
    }

    /**
     * Legacy compatibility only. New Offset records are company compensatory-
     * leave credits and are never deferred as a cash payment to a later payroll.
     * Separately approved overtime remains independently payable.
     */
    public function isDeferredOffset(): bool
    {
        return $this->adjustment_type === self::TYPE_OFFSET
            && (bool) $this->defer_to_next_payroll;
    }

    public function isCompensatoryOffset(): bool
    {
        return $this->adjustment_type === self::TYPE_OFFSET
            && ! (bool) $this->defer_to_next_payroll;
    }

    public function isApprovalRequired(): bool
    {
        return (bool) data_get(self::rulesFor((string) $this->adjustment_type), 'approval_required', false);
    }

    public function getPayrollDisplayNameAttribute(): string
    {
        return PayrollEmployeeNameFormatter::display($this->employee_name ?? null);
    }
}
