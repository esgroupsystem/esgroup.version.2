<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'reference_type',
        'reference_id',
        'movement_type',
        'qty',
        'stock_before',
        'stock_after',
        'transaction_date',
        'remarks',
        'created_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
