<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'stock_qty' => 'integer',
        'minimum_stock' => 'integer',
    ];

    /** @param Builder<self> $query */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search): void {
            $query->where('item_name', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%")
                ->orWhere('model', 'like', "%{$search}%")
                ->orWhere('part_number', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        });
    }

    /** @param Builder<self> $query */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $category === '' ? $query : $query->where('category', $category);
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
