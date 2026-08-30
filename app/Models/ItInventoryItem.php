<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $item_name
 * @property string|null $category
 * @property string|null $brand
 * @property string|null $model
 * @property string|null $part_number
 * @property string|null $unit
 * @property string|null $stock_qty
 * @property string|null $minimum_stock
 * @property string|null $description
 * @property string|null $location
 * @property bool|null $is_active
 */
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
