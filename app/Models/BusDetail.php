<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $garage
 * @property string|null $name
 * @property string|null $body_number
 * @property string|null $plate_number
 * @property int|null $active_concerns_count
 */
class BusDetail extends Model
{
    protected $fillable = ['garage', 'name', 'body_number', 'plate_number'];

    /** "Body - Plate - Name - Garage", skipping blanks; pass false to leave out the garage. */
    public function displayName(bool $withGarage = true): string
    {
        return implode(' - ', array_filter([
            $this->body_number,
            $this->plate_number,
            $this->name,
            $withGarage ? $this->garage : null,
        ]));
    }

    /** @return HasMany<JobOrder, $this> */
    public function joborders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    /** @return HasMany<PartsOut, $this> */
    public function partsOuts(): HasMany
    {
        return $this->hasMany(PartsOut::class, 'vehicle_id');
    }

    /** @return HasMany<CctvConcern, $this> */
    public function cctvConcerns(): HasMany
    {
        return $this->hasMany(CctvConcern::class, 'bus_no');
    }

    /** @return HasMany<OdometerSubmission, $this> */
    public function odometerSubmissions(): HasMany
    {
        return $this->hasMany(OdometerSubmission::class);
    }
}
