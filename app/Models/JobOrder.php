<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOrder extends Model
{
    protected $fillable = [
        'bus_detail_id', 'created_by',
        'job_name', 'job_type', 'job_datestart', 'job_time_start', 'job_time_end',
        'job_sitNumber', 'job_remarks', 'job_status', 'job_assign_person',
        'job_date_filled', 'job_creator',
        'driver_name', 'conductor_name', 'direction',
    ];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(BusDetail::class, 'bus_detail_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(JobOrderFile::class, 'job_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(JobOrderLog::class, 'joborder_id');
    }

    public function notes()
    {
        return $this->hasMany(JobOrderNote::class, 'joborder_id');
    }
}   
