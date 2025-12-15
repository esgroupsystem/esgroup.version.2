<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'product_name',
        'unit',
        'part_number',
        'details',
        'stock_qty',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
