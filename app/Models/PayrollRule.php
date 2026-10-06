<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A user-made payroll earning or deduction (Payroll Settings → Rules).
 *
 * Earnings are added to gross pay before government contributions; deductions are
 * taken from net pay after them. Each payroll item keeps a copy of the rules it used
 * in `meta.custom_rules`, so editing or deleting a rule never changes a finalized payroll.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $kind
 * @property string $method
 * @property string|null $amount
 * @property string|null $base
 * @property string|null $unit
 * @property string|null $formula
 * @property string $cutoff
 * @property string $rate_type
 * @property list<string>|null $payroll_groups
 * @property list<int>|null $employee_biometric_ids
 * @property \Illuminate\Support\Carbon|null $effective_from
 * @property \Illuminate\Support\Carbon|null $effective_to
 * @property bool $is_active
 * @property int $sort_order
 * @property string|null $notes
 */
class PayrollRule extends Model
{
    use SoftDeletes;

    public const KIND_EARNING = 'earning';

    public const KIND_DEDUCTION = 'deduction';

    public const KINDS = [
        self::KIND_EARNING => 'Earning (adds to gross pay)',
        self::KIND_DEDUCTION => 'Deduction (taken from net pay)',
    ];

    public const METHODS = [
        'fixed' => 'Fixed amount',
        'percent' => 'Percent of',
        'per_unit' => 'Amount per',
        'formula' => 'Formula',
    ];

    /** Internal cutoff keys: `second` = 26-10 (business 1st cutoff), `first` = 11-25 (business 2nd cutoff). */
    public const CUTOFFS = [
        'every' => 'Every cutoff',
        'second' => '1st cutoff (26-10) only',
        'first' => '2nd cutoff (11-25) only',
    ];

    public const RATE_TYPES = [
        'all' => 'All employees',
        'monthly' => 'Monthly-paid only',
        'daily' => 'Daily-paid only',
    ];

    /**
     * Values a rule can use. `stage` = when the value is known: `both`, or `deduction`
     * (only after government contributions, so only deduction rules can use it).
     *
     * @var array<string, array{label: string, group: string, money: bool, stage: string}>
     */
    public const VARIABLES = [
        'basic_pay' => ['label' => 'Basic pay this cutoff', 'group' => 'Pay', 'money' => true, 'stage' => 'both'],
        'gross_pay' => ['label' => 'Gross pay (earning rules: before custom earnings)', 'group' => 'Pay', 'money' => true, 'stage' => 'both'],
        'net_pay' => ['label' => 'Net pay before custom deductions', 'group' => 'Pay', 'money' => true, 'stage' => 'deduction'],
        'monthly_rate' => ['label' => 'Monthly rate', 'group' => 'Rates', 'money' => true, 'stage' => 'both'],
        'daily_rate' => ['label' => 'Daily rate', 'group' => 'Rates', 'money' => true, 'stage' => 'both'],
        'hourly_rate' => ['label' => 'Hourly rate', 'group' => 'Rates', 'money' => true, 'stage' => 'both'],
        'minute_rate' => ['label' => 'Per-minute rate', 'group' => 'Rates', 'money' => true, 'stage' => 'both'],
        'overtime_pay' => ['label' => 'Overtime pay', 'group' => 'Pay', 'money' => true, 'stage' => 'both'],
        'night_diff_pay' => ['label' => 'Night differential pay', 'group' => 'Pay', 'money' => true, 'stage' => 'both'],
        'holiday_pay' => ['label' => 'Holiday pay', 'group' => 'Pay', 'money' => true, 'stage' => 'both'],
        'rest_day_pay' => ['label' => 'Rest day pay', 'group' => 'Pay', 'money' => true, 'stage' => 'both'],
        'leave_pay' => ['label' => 'Leave pay', 'group' => 'Pay', 'money' => true, 'stage' => 'both'],
        'allowance' => ['label' => 'Allowance this cutoff', 'group' => 'Pay', 'money' => true, 'stage' => 'both'],
        'attendance_loss' => ['label' => 'Late + undertime + absence amount', 'group' => 'Pay', 'money' => true, 'stage' => 'both'],
        'government_deductions' => ['label' => 'SSS + PhilHealth + Pag-IBIG (employee)', 'group' => 'Pay', 'money' => true, 'stage' => 'deduction'],
        'days_worked' => ['label' => 'Days worked', 'group' => 'Attendance', 'money' => false, 'stage' => 'both'],
        'payable_days' => ['label' => 'Payable days', 'group' => 'Attendance', 'money' => false, 'stage' => 'both'],
        'payable_hours' => ['label' => 'Payable hours', 'group' => 'Attendance', 'money' => false, 'stage' => 'both'],
        'scheduled_days' => ['label' => 'Scheduled work days', 'group' => 'Attendance', 'money' => false, 'stage' => 'both'],
        'days_absent' => ['label' => 'Days absent', 'group' => 'Attendance', 'money' => false, 'stage' => 'both'],
        'leave_days' => ['label' => 'Leave days', 'group' => 'Attendance', 'money' => false, 'stage' => 'both'],
        'minutes_late' => ['label' => 'Minutes late', 'group' => 'Attendance', 'money' => false, 'stage' => 'both'],
        'minutes_undertime' => ['label' => 'Minutes undertime', 'group' => 'Attendance', 'money' => false, 'stage' => 'both'],
        'ot_hours' => ['label' => 'Approved overtime hours', 'group' => 'Attendance', 'money' => false, 'stage' => 'both'],
        'night_hours' => ['label' => 'Night differential hours', 'group' => 'Attendance', 'money' => false, 'stage' => 'both'],
        'holidays_worked' => ['label' => 'Holidays worked', 'group' => 'Attendance', 'money' => false, 'stage' => 'both'],
        'rest_days_worked' => ['label' => 'Rest days worked', 'group' => 'Attendance', 'money' => false, 'stage' => 'both'],
        'is_monthly' => ['label' => '1 if monthly-paid, else 0', 'group' => 'Other', 'money' => false, 'stage' => 'both'],
        'cutoff' => ['label' => '1 = 1st cutoff (26-10), 2 = 2nd cutoff (11-25)', 'group' => 'Other', 'money' => false, 'stage' => 'both'],
        'month' => ['label' => 'Payroll month (1-12)', 'group' => 'Other', 'money' => false, 'stage' => 'both'],
    ];

    protected $fillable = [
        'name',
        'code',
        'kind',
        'method',
        'amount',
        'base',
        'unit',
        'formula',
        'cutoff',
        'rate_type',
        'payroll_groups',
        'employee_biometric_ids',
        'effective_from',
        'effective_to',
        'is_active',
        'sort_order',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'payroll_groups' => 'array',
            'employee_biometric_ids' => 'array',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return list<string> value names a rule of this kind may use */
    public static function variableNames(string $kind): array
    {
        return array_keys(array_filter(
            self::VARIABLES,
            fn (array $variable): bool => $variable['stage'] === 'both' || $kind === self::KIND_DEDUCTION
        ));
    }

    /** @return array<string, string> money values (for "percent of") */
    public static function moneyVariables(): array
    {
        return collect(self::VARIABLES)->filter(fn (array $variable): bool => $variable['money'])->map->label->all();
    }

    /** @return array<string, string> count values (for "amount per") */
    public static function unitVariables(): array
    {
        return collect(self::VARIABLES)->reject(fn (array $variable): bool => $variable['money'])->map->label->all();
    }

    public function isEarning(): bool
    {
        return $this->kind === self::KIND_EARNING;
    }

    /** Plain description of how the rule computes, e.g. "5% of basic pay". */
    public function describe(): string
    {
        $amount = (float) $this->amount;

        return match ($this->method) {
            'fixed' => '₱'.number_format($amount, 2).' per cutoff',
            'percent' => rtrim(rtrim(number_format($amount, 4), '0'), '.').'% of '.mb_strtolower(self::VARIABLES[$this->base]['label'] ?? (string) $this->base),
            'per_unit' => '₱'.number_format($amount, 2).' × '.mb_strtolower(self::VARIABLES[$this->unit]['label'] ?? (string) $this->unit),
            'formula' => (string) $this->formula,
            default => '',
        };
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
