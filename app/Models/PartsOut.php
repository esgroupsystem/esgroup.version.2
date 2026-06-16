<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function items()
    {
        return $this->hasMany(PartsOutItem::class, 'parts_out_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(BusDetail::class, 'vehicle_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rollbackUser()
    {
        return $this->belongsTo(User::class, 'rolled_back_by');
    }

    public function location()
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
