<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string|null $parts_out_id
 * @property string|null $product_id
 * @property string|null $qty_used
 * @property string|null $stock_before
 * @property string|null $stock_after
 * @property string|null $remarks
 */
class PartsOutItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parts_out_id',
        'product_id',
        'qty_used',
        'stock_before',
        'stock_after',
        'remarks',
    ];

    /** @return BelongsTo<PartsOut, $this> */
    public function partsOut(): BelongsTo
    {
        return $this->belongsTo(PartsOut::class, 'parts_out_id');
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
