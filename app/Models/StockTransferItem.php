<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'qty',
        'status',
        'rolled_back_at',
        'rolled_back_by',
    ];

    protected $casts = [
        'qty' => 'integer',
        'rolled_back_at' => 'datetime',
    ];

    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function rollbackUser()
    {
        return $this->belongsTo(User::class, 'rolled_back_by');
    }
}
