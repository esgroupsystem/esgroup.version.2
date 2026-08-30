<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $trip_code
 * @property string|null $bus_number
 * @property string|null $driver_name
 * @property string|null $started_at
 * @property string|null $ended_at
 * @property string|null $user_id
 */
class Trip extends Model
{
    protected $fillable = [
        'trip_code',
        'bus_number',
        'driver_name',
        'started_at',
        'ended_at',
        'user_id',
    ];
}
