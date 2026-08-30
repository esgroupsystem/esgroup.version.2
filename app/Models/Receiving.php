<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $receiving_number
 * @property string|null $location_id
 * @property string|null $delivered_by
 * @property string|null $delivery_date
 * @property string|null $remarks
 * @property string|null $proof_image
 * @property string|null $received_by
 */
class Receiving extends Model
{
    protected $fillable = [
        'receiving_number',
        'location_id',
        'delivered_by',
        'delivery_date',
        'remarks',
        'proof_image',
        'received_by',
    ];

    /** @return HasMany<ReceivingItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ReceivingItem::class);
    }

    /** @return BelongsTo<User, $this> */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /** @return BelongsTo<Location, $this> */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
