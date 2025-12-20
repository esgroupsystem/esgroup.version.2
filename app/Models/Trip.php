<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
