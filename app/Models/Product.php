<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'product_name',
        'supplier_name',
        'unit',
        'part_number',
        'details',
        'stock_qty',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class, 'product_id');
    }

    public function transferItems()
    {
        return $this->hasMany(StockTransferItem::class, 'product_id');
    }

    public function getStockAt($locationId)
    {
        return $this->stocks()->where('location_id', $locationId)->value('qty') ?? 0;
    }
}
