<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    protected $fillable = [
        'payroll_number',
        'cutoff_month',
        'cutoff_year',
        'cutoff_type',
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

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function paymentLogs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    public function reportLogs(): HasMany
    {
        return $this->hasMany(PayrollReportLog::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    protected function cutoffLabel(): Attribute
    {
        return Attribute::get(function (): string {
            $type = $this->cutoff_type === 'first' ? '1st Cutoff' : '2nd Cutoff';
            $monthLabel = now()->setDate((int) $this->cutoff_year, (int) $this->cutoff_month, 1)->format('F Y');

            return $type.' - '.$monthLabel;
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
}
