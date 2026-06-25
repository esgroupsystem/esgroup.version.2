<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bus extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_MECHANICAL_BREAKDOWN = 'mechanical_breakdown';

    public const STATUS_ACCIDENT_RELATED_BREAKDOWN = 'accident_related_breakdown';

    public const STATUS_ON_HOLD_PLATE_REGISTRATION = 'on_hold_plate_registration';

    public const SALE_NOT_FOR_SALE = 'not_for_sale';

    public const SALE_FOR_SALE = 'for_sale';

    protected $fillable = [
        'bus_no',
        'plate_no',
        'company',
        'garage',
        'chassis_number',
        'engine_number',
        'case_number',
        'operational_status',
        'sale_status',
        'monitoring_remarks',
        'status_updated_at',
    ];

    protected $casts = [
        'status_updated_at' => 'datetime',
    ];

    public static function operationalStatusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_MECHANICAL_BREAKDOWN => 'Mechanical Breakdown',
            self::STATUS_ACCIDENT_RELATED_BREAKDOWN => 'Accident Related Breakdown',
            self::STATUS_ON_HOLD_PLATE_REGISTRATION => 'On Hold due to Plate Reg.',
        ];
    }

    public static function saleStatusOptions(): array
    {
        return [
            self::SALE_NOT_FOR_SALE => 'Not For Sale',
            self::SALE_FOR_SALE => 'For Sale',
        ];
    }

    public function forSaleRecords(): HasMany
    {
        return $this->hasMany(BusForSaleRecord::class);
    }

    public function forSaleRecord(): HasOne
    {
        return $this->hasOne(BusForSaleRecord::class);
    }

    public function currentForSaleRecord(): HasOne
    {
        return $this->hasOne(BusForSaleRecord::class)->latestOfMany();
    }

    public function getOperationalStatusLabelAttribute(): string
    {
        return self::operationalStatusOptions()[$this->operational_status] ?? 'Unknown';
    }

    public function getSaleStatusLabelAttribute(): string
    {
        return self::saleStatusOptions()[$this->sale_status] ?? 'Unknown';
    }

    public function getOperationalStatusBadgeClassAttribute(): string
    {
        return match ($this->operational_status) {
            self::STATUS_ACTIVE => 'badge-subtle-success text-success',
            self::STATUS_MECHANICAL_BREAKDOWN => 'badge-subtle-warning text-warning',
            self::STATUS_ACCIDENT_RELATED_BREAKDOWN => 'badge-subtle-danger text-danger',
            self::STATUS_ON_HOLD_PLATE_REGISTRATION => 'badge-subtle-info text-info',
            default => 'badge-subtle-secondary text-secondary',
        };
    }

    public function getSaleStatusBadgeClassAttribute(): string
    {
        return match ($this->sale_status) {
            self::SALE_FOR_SALE => 'badge-subtle-danger text-danger',
            default => 'badge-subtle-secondary text-secondary',
        };
    }
}
