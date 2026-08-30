<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $transfer_number
 * @property string|null $from_location_id
 * @property string|null $to_location_id
 * @property \Carbon\CarbonInterface|null $transfer_date
 * @property string|null $requested_by
 * @property string|null $received_by
 * @property string|null $remarks
 * @property string|null $status
 * @property \Carbon\CarbonInterface|null $rolled_back_at
 * @property string|null $rolled_back_by
 * @property string|null $rollback_reason
 * @property string|null $created_by
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StockTransferItem> $items
 */
class StockTransfer extends Model
{
    protected $fillable = [
        'transfer_number',
        'from_location_id',
        'to_location_id',
        'transfer_date',
        'requested_by',
        'received_by',
        'remarks',
        'status',
        'rolled_back_at',
        'rolled_back_by',
        'rollback_reason',
        'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'rolled_back_at' => 'datetime',
    ];

    /** @return BelongsTo<Location, $this> */
    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    /** @return BelongsTo<Location, $this> */
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    /** @return HasMany<StockTransferItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function rollbackUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rolled_back_by');
    }

    public function isRolledBack(): bool
    {
        return $this->status === 'rolled_back' || ! is_null($this->rolled_back_at);
    }
}
