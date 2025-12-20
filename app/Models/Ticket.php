<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'trip_id',
        'from_location',
        'to_location',
        'fare',
        'user_id',
        'issued_at',
    ];
}
