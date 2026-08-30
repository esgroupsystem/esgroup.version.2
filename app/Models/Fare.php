<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $from_location
 * @property string|null $to_location
 * @property string|null $fare
 */
class Fare extends Model
{
    protected $fillable = [
        'from_location',
        'to_location',
        'fare',
    ];
}
