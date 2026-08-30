<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $payroll_number
 * @property int|null $cutoff_month
 * @property int|null $cutoff_year
 * @property string|null $cutoff_type
 * @property int|string|null $garage_group
 * @property int|null $contribution_month
 * @property int|null $contribution_year
 * @property \Carbon\CarbonInterface|null $period_start
 * @property \Carbon\CarbonInterface|null $period_end
 * @property string|null $status
 * @property-read int|string|null $garage_group
 * @property-read string $cutoff_label
 * @property-read string $contribution_label
 * @property-read string $garage_group_label
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PayrollItem> $items
 * @property-read User|null $generator
 * @property-read User|null $finalizer

 * @property string|null $remarks
 * @property string|null $generated_by
 * @property \Carbon\CarbonInterface|null $generated_at
 * @property string|null $finalized_by
 * @property \Carbon\CarbonInterface|null $finalized_at
 * @property array<string, mixed>|null $meta
 */
class Payroll extends Model
{
    protected $fillable = [
        'payroll_number',
        'cutoff_month',
        'cutoff_year',
        'cutoff_type',
        'garage_group',
        'contribution_month',
        'contribution_year',
        'period_start',
        'period_end',
        'remarks',
        'generated_by',
        'generated_at',
        'finalized_by',
        'finalized_at',
        'status',
        'meta',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'datetime',
        'finalized_at' => 'datetime',
        'meta' => 'array',
    ];

    /** @return HasMany<PayrollItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    /** @return HasMany<PaymentLog, $this> */
    public function paymentLogs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    /** @return HasMany<PayrollReportLog, $this> */
    public function reportLogs(): HasMany
    {
        return $this->hasMany(PayrollReportLog::class);
    }

    /** @return HasMany<BenefitContributionRecord, $this> */
    public function benefitContributionRecords(): HasMany
    {
        return $this->hasMany(BenefitContributionRecord::class);
    }

    /** @return BelongsTo<User, $this> */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /** @return BelongsTo<User, $this> */
    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    protected function cutoffLabel(): Attribute
    {
        return Attribute::get(function (): string {
            // Legacy database mapping is intentionally preserved:
            // `first`  = 11-25 = business 2nd cutoff
            // `second` = 26-10 = business 1st cutoff
            $type = (string) $this->cutoff_type;
            $display = (string) config(
                "payroll.cutoff_display.{$type}.full",
                $type === 'first' ? '2nd Cutoff (11-25)' : '1st Cutoff (26-10)'
            );

            $cycleMonth = $this->contribution_month && $this->contribution_year
                ? now()->setDate((int) $this->contribution_year, (int) $this->contribution_month, 1)
                : now()->setDate((int) $this->cutoff_year, (int) $this->cutoff_month, 1);

            $range = $this->period_start && $this->period_end
                ? $this->period_start->format('M d').' - '.$this->period_end->format('M d, Y')
                : null;

            return $display
                .($range ? ' | '.$range : '')
                .' - '.$cycleMonth->format('F Y');
        });
    }

    protected function contributionLabel(): Attribute
    {
        return Attribute::get(function (): string {
            if (! $this->contribution_month || ! $this->contribution_year) {
                return $this->cutoff_label;
            }

            return now()->setDate((int) $this->contribution_year, (int) $this->contribution_month, 1)->format('F Y');
        });
    }

    public function getGarageGroupLabelAttribute(): string
    {
        return match ((int) $this->garage_group) {
            1 => 'Mirasol / Balintawak Payroll',
            2 => 'Gonzales Payroll',
            default => 'Unknown Group',
        };
    }
}
