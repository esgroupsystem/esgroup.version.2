<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartsOutItem extends Model
{
    protected $fillable = [
        'parts_out_id',
        'product_id',
        'qty_used',
        'stock_before',
        'stock_after',
        'remarks',
    ];

    public function partsOut()
    {
        return $this->belongsTo(PartsOut::class, 'parts_out_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
