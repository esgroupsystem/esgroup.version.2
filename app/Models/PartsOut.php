<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string|null $parts_out_number
 * @property string|null $vehicle_id
 * @property string|null $location_id
 * @property string|null $mechanic_name
 * @property string|null $requested_by
 * @property \Carbon\CarbonInterface|null $issued_date
 * @property string|null $job_order_no
 * @property string|null $odometer
 * @property string|null $purpose
 * @property string|null $remarks
 * @property string|null $status
 * @property string|null $created_by
 * @property \Carbon\CarbonInterface|null $rolled_back_at
 * @property string|null $rolled_back_by
 * @property string|null $rollback_reason
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PartsOutItem> $items
 */
class PartsOut extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parts_out_number',
        'vehicle_id',
        'location_id',
        'mechanic_name',
        'requested_by',
        'issued_date',
        'job_order_no',
        'odometer',
        'purpose',
        'remarks',
        'status',
        'created_by',
        'rolled_back_at',
        'rolled_back_by',
        'rollback_reason',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'rolled_back_at' => 'datetime',
    ];

    /** @return HasMany<PartsOutItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PartsOutItem::class, 'parts_out_id');
    }

    /** @return BelongsTo<BusDetail, $this> */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(BusDetail::class, 'vehicle_id');
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

    /** @return BelongsTo<Location, $this> */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (PartsOut $partsOut) {
            if (! $partsOut->isForceDeleting()) {
                $partsOut->items()->delete();
            }
        });

        static::forceDeleting(function (PartsOut $partsOut) {
            $partsOut->items()->forceDelete();
        });
    }
}
