<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'qty',
        'purchased_qty',
        'received_qty',
        'store_name',
        'removed',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function receives()
    {
        return $this->hasMany(PurchaseReceive::class, 'purchase_order_item_id');
    }
}
