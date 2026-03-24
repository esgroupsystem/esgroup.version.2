<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
        'is_active',
    ];

    public function productStocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'from_location_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'to_location_id');
    }
}