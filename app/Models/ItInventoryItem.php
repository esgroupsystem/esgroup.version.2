<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItInventoryItem extends Model
{
    protected $fillable = [
        'item_name',
        'category',
        'brand',
        'model',
        'part_number',
        'unit',
        'stock_qty',
        'minimum_stock',
        'description',
        'location',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
