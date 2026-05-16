<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DieselStock extends Model
{
    protected $fillable = [
        'date',
        'type',
        'liters',
        'unit_cost',
        'total_cost',
        'bus_detail_id',
        'odometer_submission_id',
        'reference_no',
        'remarks',
        'encoded_by',
    ];

    protected $casts = [
        'date' => 'date',
        'liters' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function bus()
    {
        return $this->belongsTo(BusDetail::class, 'bus_detail_id');
    }

    public function odometerSubmission()
    {
        return $this->belongsTo(OdometerSubmission::class);
    }

    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }
}
