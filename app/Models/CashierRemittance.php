<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $bus_number
 * @property string|null $driver_name
 * @property string|null $conductor_name
 * @property string|null $dispatcher_name
 * @property string|null $time_in
 * @property string|null $time_out
 * @property string|null $total_collection
 * @property string|null $diesel
 * @property string|null $user_id
 * @property string|null $synced_at
 */
class CashierRemittance extends Model
{
    protected $fillable = [
        'bus_number',
        'driver_name',
        'conductor_name',
        'dispatcher_name',
        'time_in',
        'time_out',
        'total_collection',
        'diesel',
        'user_id',
        'synced_at',
    ];
}
