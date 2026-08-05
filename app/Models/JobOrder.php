<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOrder extends Model
{
    protected $fillable = [
        'bus_detail_id',
        'created_by',

        'job_name',
        'job_type',
        'job_datestart',
        'job_time_start',
        'job_time_end',
        'job_sitNumber',
        'job_remarks',

        'job_status',
        'approval_status',
        'approved_by',
        'approved_at',

        'job_assign_person',
        'job_date_filled',
        'job_creator',

        'driver_name',
        'conductor_name',
        'direction',
    ];

    protected function casts(): array
    {
        return [
            'bus_detail_id' => 'integer',
            'created_by' => 'integer',
            'approved_by' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(
            BusDetail::class,
            'bus_detail_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function files(): HasMany
    {
        return $this->hasMany(
            JobOrderFile::class,
            'job_id'
        );
    }

    public function logs(): HasMany
    {
        return $this->hasMany(
            JobOrderLog::class,
            'joborder_id'
        );
    }

    public function notes(): HasMany
    {
        return $this->hasMany(
            JobOrderNote::class,
            'joborder_id'
        );
    }
}
