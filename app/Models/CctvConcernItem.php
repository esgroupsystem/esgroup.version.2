<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CctvConcernItem extends Model
{
    protected $fillable = [
        'cctv_concern_id',
        'it_inventory_item_id',
        'qty_used',
        'remarks',
    ];

    public function concern()
    {
        return $this->belongsTo(CctvConcern::class, 'cctv_concern_id');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(ItInventoryItem::class, 'it_inventory_item_id');
    }
}