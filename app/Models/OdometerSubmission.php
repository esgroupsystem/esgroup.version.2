<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
