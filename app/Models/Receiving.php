<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receiving extends Model
{
    protected $fillable = [
        'receiving_number',
        'location_id',
        'delivered_by',
        'delivery_date',
        'remarks',
        'proof_image',
        'received_by',
    ];

    public function items()
    {
        return $this->hasMany(ReceivingItem::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
