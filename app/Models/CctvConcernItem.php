<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $cctv_concern_id
 * @property string|null $it_inventory_item_id
 * @property string|null $qty_used
 * @property string|null $remarks
 */
class CctvConcernItem extends Model
{
    protected $fillable = [
        'cctv_concern_id',
        'it_inventory_item_id',
        'qty_used',
        'remarks',
    ];

    /** @return BelongsTo<CctvConcern, $this> */
    public function concern(): BelongsTo
    {
        return $this->belongsTo(CctvConcern::class, 'cctv_concern_id');
    }

    /** @return BelongsTo<ItInventoryItem, $this> */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(ItInventoryItem::class, 'it_inventory_item_id');
    }
}
