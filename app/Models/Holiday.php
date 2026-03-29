<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'actual_date',
        'observed_date',
        'holiday_type',
        'is_moved',
        'not_worked_multiplier',
        'worked_multiplier',
        'source_proclamation',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'actual_date' => 'date',
        'observed_date' => 'date',
        'is_moved' => 'boolean',
        'is_active' => 'boolean',
        'not_worked_multiplier' => 'decimal:2',
        'worked_multiplier' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnDate($query, string|Carbon $date)
    {
        $date = $date instanceof Carbon ? $date->toDateString() : $date;

        return $query->whereDate('observed_date', $date);
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return $this->holiday_type === 'regular'
            ? 'badge bg-danger-subtle text-danger'
            : 'badge bg-warning-subtle text-warning';
    }
}
