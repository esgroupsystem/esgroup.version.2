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
 * @property string|null $display_name
 * @property string|null $status_summary
 * @property int $total_issues
 * @property int $completed_count
 */
class BusDetail extends Model
{
    protected $fillable = ['garage', 'name', 'body_number', 'plate_number'];

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
