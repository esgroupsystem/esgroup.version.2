<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartsOut extends Model
{
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
    ];

    protected $casts = [
        'issued_date' => 'date',
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

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
