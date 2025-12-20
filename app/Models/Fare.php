<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fare extends Model
{
    protected $fillable = [
        'from_location',
        'to_location',
        'fare',
    ];
}
