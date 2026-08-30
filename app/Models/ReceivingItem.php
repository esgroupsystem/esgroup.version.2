<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $receiving_id
 * @property string|null $product_id
 * @property string|null $qty_delivered
 * @property string|null $qty_rolled_back
 * @property \Carbon\CarbonInterface|null $last_rolled_back_at
 */
class ReceivingItem extends Model
{
    protected $fillable = [
        'receiving_id',
        'product_id',
        'qty_delivered',
        'qty_rolled_back',
        'last_rolled_back_at',
    ];

    protected $casts = [
        'last_rolled_back_at' => 'datetime',
    ];

    /** @return BelongsTo<Receiving, $this> */
    public function receiving(): BelongsTo
    {
        return $this->belongsTo(Receiving::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
