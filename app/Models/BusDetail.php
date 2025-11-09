<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusDetail extends Model
{
    protected $fillable = ['garage','name','body_number','plate_number'];

    public function joborders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }
}
