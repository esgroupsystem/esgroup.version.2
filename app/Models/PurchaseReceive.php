<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReceive extends Model
{
    protected $fillable = [
        'purchase_order_item_id',
        'qty_received',
        'received_by'
    ];

    public function item()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
