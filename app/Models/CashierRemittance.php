<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
