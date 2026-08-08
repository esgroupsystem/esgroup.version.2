<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    public const TYPE_REGULAR = 'regular';

    public const TYPE_SPECIAL = 'special';

    public const STANDARD_MULTIPLIERS = [
        self::TYPE_REGULAR => [
            'not_worked_multiplier' => 1.00,
            'worked_multiplier' => 2.00,
        ],
        self::TYPE_SPECIAL => [
            'not_worked_multiplier' => 0.00,
            'worked_multiplier' => 1.30,
        ],
    ];

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

    public static function standardMultipliers(string $type): array
    {
        return self::STANDARD_MULTIPLIERS[$type]
            ?? self::STANDARD_MULTIPLIERS[self::TYPE_REGULAR];
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return $this->holiday_type === self::TYPE_REGULAR
            ? 'badge bg-danger-subtle text-danger'
            : 'badge bg-warning-subtle text-warning';
    }
}
