<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $purchase_order_item_id
 * @property string|null $qty_received
 * @property string|null $received_by
 */
class PurchaseReceive extends Model
{
    protected $fillable = [
        'purchase_order_item_id',
        'qty_received',
        'received_by',
    ];

    /** @return BelongsTo<PurchaseOrderItem, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    /** @return BelongsTo<User, $this> */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
