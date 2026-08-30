<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $user_id
 * @property string|null $bus_detail_id
 * @property string|null $new_odometer
 * @property string|null $driver_name
 * @property string|null $diesel_consumption
 * @property string|null $date_bus_deployed
 * @property string|null $date
 * @property string|null $time
 */
class OdometerSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'bus_detail_id',
        'new_odometer',
        'driver_name',
        'diesel_consumption',
        'date_bus_deployed',
        'date',
        'time',
    ];

    /** @return BelongsTo<BusDetail, $this> */
    public function busDetail(): BelongsTo
    {
        return $this->belongsTo(BusDetail::class);
    }
}
