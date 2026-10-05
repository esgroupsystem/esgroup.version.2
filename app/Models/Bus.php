<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string|null $bus_no
 * @property string|null $plate_no
 * @property string|null $company
 * @property string|null $garage
 * @property string $operational_status
 * @property string $sale_status
 * @property int|null $not_for_sale
 * @property int|null $mechanical_breakdown
 * @property int|null $accident_related
 * @property int|null $on_hold
 * @property int|null $for_sale
 * @property int|null $total_units
 * @property string|null $group_name
 * @property-read string $operational_status_label
 * @property-read string $sale_status_label

 * @property string|null $chassis_number
 * @property string|null $engine_number
 * @property string|null $case_number
 * @property string|null $monitoring_remarks
 * @property \Carbon\CarbonInterface|null $status_updated_at
 */
class Bus extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_MECHANICAL_BREAKDOWN = 'mechanical_breakdown';

    public const STATUS_ACCIDENT_RELATED_BREAKDOWN = 'accident_related_breakdown';

    public const STATUS_ON_HOLD_PLATE_REGISTRATION = 'on_hold_plate_registration';

    // NEW
    public const STATUS_FOR_RENTAL_CHARTER = 'for_rental_charter';

    public const STATUS_INACTIVE = 'inactive';

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
            self::STATUS_FOR_RENTAL_CHARTER => 'For Rental/Charter',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public static function saleStatusOptions(): array
    {
        return [
            self::SALE_NOT_FOR_SALE => 'Not For Sale',
            self::SALE_FOR_SALE => 'For Sale',
        ];
    }

    /** @return HasMany<BusForSaleRecord, $this> */
    public function forSaleRecords(): HasMany
    {
        return $this->hasMany(BusForSaleRecord::class);
    }

    /** @return HasOne<BusForSaleRecord, $this> */
    public function forSaleRecord(): HasOne
    {
        return $this->hasOne(BusForSaleRecord::class);
    }

    /** @return HasOne<BusForSaleRecord, $this> */
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

    /** @return HasMany<JobOrderMaintenance, $this> */
    public function jobOrderMaintenances(): HasMany
    {
        return $this->hasMany(JobOrderMaintenance::class);
    }

    /** @return HasOne<JobOrderMaintenance, $this> */
    public function latestJobOrderMaintenance(): HasOne
    {
        return $this->hasOne(JobOrderMaintenance::class)->latestOfMany();
    }

    /** @return HasOne<JobOrderMaintenance, $this> */
    public function latestJobOrderMaintenanceWithOdometer(): HasOne
    {
        return $this->hasOne(JobOrderMaintenance::class)
            ->whereNotNull('odometer_reading')
            ->latestOfMany();
    }
}
