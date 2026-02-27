<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrOffense extends Model
{
    protected $fillable = [
        'section',
        'offense_description',
        'offense_type',
        'offense_gravity',
    ];
}
