<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $trip_id
 * @property string|null $from_location
 * @property string|null $to_location
 * @property string|null $fare
 * @property string|null $user_id
 * @property string|null $issued_at
 */
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
