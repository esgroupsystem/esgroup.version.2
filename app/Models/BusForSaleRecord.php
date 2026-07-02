<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusForSaleRecord extends Model
{
    protected $fillable = [
        'bus_id',
        'bus_no',
        'plate_no',
        'company',
        'garage',
        'status',
        'storage_area',
        'breakdown_start_date',
        'breakdown_end_date',
        'column_11',
        'days_in_breakdown',
        'unit_location',
        'progress',
        'remarks',
    ];

    protected $casts = [
        'breakdown_start_date' => 'date',
        'breakdown_end_date' => 'date',
        'days_in_breakdown' => 'integer',
    ];

    protected $appends = [
        'status_label',
        'status_badge_class',
        'live_days_in_breakdown',
    ];

    public static function statusOptions(): array
    {
        return [
            Bus::STATUS_ACTIVE => 'Running Condition',
            Bus::STATUS_MECHANICAL_BREAKDOWN => 'Mechanical Breakdown',
            Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN => 'Accident Related Breakdown',
            Bus::STATUS_ON_HOLD_PLATE_REGISTRATION => 'On Hold due to Plate Reg.',
            Bus::STATUS_FOR_RENTAL_CHARTER => 'For Rental/Charter',
            Bus::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            Bus::STATUS_ACTIVE => 'badge-subtle-success text-success',
            Bus::STATUS_MECHANICAL_BREAKDOWN => 'badge-subtle-warning text-warning',
            Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN => 'badge-subtle-danger text-danger',
            Bus::STATUS_ON_HOLD_PLATE_REGISTRATION => 'badge-subtle-info text-info',
            default => 'badge-subtle-secondary text-secondary',
        };
    }

    public function getLiveDaysInBreakdownAttribute(): int
    {
        if (! $this->breakdown_start_date) {
            return 0;
        }

        $startDate = Carbon::parse($this->breakdown_start_date)->startOfDay();

        $endDate = $this->breakdown_end_date
            ? Carbon::parse($this->breakdown_end_date)->startOfDay()
            : now()->startOfDay();

        if ($endDate->lessThan($startDate)) {
            return 0;
        }

        return (int) $startDate->diffInDays($endDate);
    }
}
