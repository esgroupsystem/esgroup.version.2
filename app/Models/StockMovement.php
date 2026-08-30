<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $product_id
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property string|null $movement_type
 * @property string|null $qty
 * @property string|null $stock_before
 * @property string|null $stock_after
 * @property string|null $transaction_date
 * @property string|null $remarks
 * @property string|null $created_by
 */
class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'reference_type',
        'reference_id',
        'movement_type',
        'qty',
        'stock_before',
        'stock_after',
        'transaction_date',
        'remarks',
        'created_by',
    ];

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
