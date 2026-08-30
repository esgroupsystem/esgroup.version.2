<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int|null $category_id
 * @property string $product_name
 * @property string|null $supplier_name
 * @property string|null $unit
 * @property string|null $part_number
 * @property string|null $details
 * @property string|null $stock_qty
 * @property array<int|string, int> $location_stocks
 * @property int $main_qty
 * @property int $balintawak_qty
 * @property int $total_stock
 * @property string|null $stock_status
 * @property string|null $transfer_suggestion
 */
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

    protected static function booted()
    {
        static::created(function ($product) {
            $locations = Location::all();

            foreach ($locations as $location) {
                ProductStock::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'location_id' => $location->id,
                    ],
                    [
                        'qty' => 0,
                    ]
                );
            }
        });
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<ProductStock, $this> */
    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class, 'product_id');
    }

    /** @return HasMany<StockTransferItem, $this> */
    public function transferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'product_id');
    }

    public function getStockAt(int $locationId): int
    {
        return (int) ($this->stocks()
            ->where('location_id', $locationId)
            ->value('qty') ?? 0);
    }

    public function totalStock(): int
    {
        return (int) $this->stocks()->sum('qty');
    }
}
