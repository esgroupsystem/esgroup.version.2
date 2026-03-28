<?php

namespace App\Models;

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
        'period_start',
        'period_end',
        'status',
        'remarks',
        'generated_by',
        'finalized_by',
        'generated_at',
        'finalized_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function getCutoffLabelAttribute(): string
    {
        $type = $this->cutoff_type === 'first' ? '1st Cutoff' : '2nd Cutoff';

        return "{$type} - {$this->period_start?->format('M d, Y')} to {$this->period_end?->format('M d, Y')}";
    }
}
