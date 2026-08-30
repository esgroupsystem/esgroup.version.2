<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $purchase_order_id
 * @property string|null $product_id
 * @property string|null $qty
 * @property string|null $purchased_qty
 * @property string|null $received_qty
 * @property string|null $store_name
 * @property string|null $removed
 */
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

    /** @return BelongsTo<PurchaseOrder, $this> */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return HasMany<PurchaseReceive, $this> */
    public function receives(): HasMany
    {
        return $this->hasMany(PurchaseReceive::class, 'purchase_order_item_id');
    }
}
