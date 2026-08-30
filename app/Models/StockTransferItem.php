<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $stock_transfer_id
 * @property string|null $product_id
 * @property int|null $qty
 * @property string|null $status
 * @property \Carbon\CarbonInterface|null $rolled_back_at
 * @property string|null $rolled_back_by
 */
class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'qty',
        'status',
        'rolled_back_at',
        'rolled_back_by',
    ];

    protected $casts = [
        'qty' => 'integer',
        'rolled_back_at' => 'datetime',
    ];

    /** @return BelongsTo<StockTransfer, $this> */
    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<User, $this> */
    public function rollbackUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rolled_back_by');
    }
}
