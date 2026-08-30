<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $requester_id
 * @property-read User|null $requester
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchaseOrderItem> $items

 * @property string|null $po_number
 * @property string|null $garage
 * @property string|null $status
 */
class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'garage',
        'requester_id',
        'status',
    ];

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /** @return HasMany<PurchaseOrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
