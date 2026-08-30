<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int|null $bus_detail_id
 * @property int|null $created_by
 * @property string|null $job_name
 * @property string|null $job_type
 * @property string|null $job_datestart
 * @property string|null $job_time_start
 * @property string|null $job_time_end
 * @property string|null $job_sitNumber
 * @property string|null $job_remarks
 * @property string|null $job_status
 * @property string|null $approval_status
 * @property int|null $approved_by
 * @property \Carbon\CarbonInterface|null $approved_at
 * @property string|null $job_assign_person
 * @property string|null $job_date_filled
 * @property string|null $job_creator
 * @property string|null $driver_name
 * @property string|null $conductor_name
 * @property string|null $direction
 */
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

    /** @return BelongsTo<BusDetail, $this> */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(
            BusDetail::class,
            'bus_detail_id'
        );
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    /** @return HasMany<JobOrderFile, $this> */
    public function files(): HasMany
    {
        return $this->hasMany(
            JobOrderFile::class,
            'job_id'
        );
    }

    /** @return HasMany<JobOrderLog, $this> */
    public function logs(): HasMany
    {
        return $this->hasMany(
            JobOrderLog::class,
            'joborder_id'
        );
    }

    /** @return HasMany<JobOrderNote, $this> */
    public function notes(): HasMany
    {
        return $this->hasMany(
            JobOrderNote::class,
            'joborder_id'
        );
    }
}
