<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rollbackUser()
    {
        return $this->belongsTo(User::class, 'rolled_back_by');
    }

    public function isRolledBack(): bool
    {
        return $this->status === 'rolled_back' || ! is_null($this->rolled_back_at);
    }
}
