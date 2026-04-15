<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivingItem extends Model
{
    protected $fillable = [
        'receiving_id',
        'product_id',
        'qty_delivered',
        'qty_rolled_back',
        'last_rolled_back_at',
    ];

    protected $casts = [
        'last_rolled_back_at' => 'datetime',
    ];

    public function receiving()
    {
        return $this->belongsTo(Receiving::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
