<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\JobOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \App\Enums\JobOrderStatus $status
 * @property \Carbon\CarbonInterface $started_at
 * @property \Carbon\CarbonInterface|null $ended_at
 * @property-read int $duration_minutes
 * @property-read string $duration_label

 * @property string|null $job_order_maintenance_id
 * @property int|null $changed_by
 */
class JobOrderMaintenanceStatusPeriod extends Model
{
    protected $fillable = [
        'job_order_maintenance_id',
        'status',
        'started_at',
        'ended_at',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobOrderStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'changed_by' => 'integer',
        ];
    }

    /** @return BelongsTo<JobOrderMaintenance, $this> */
    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrderMaintenance::class, 'job_order_maintenance_id');
    }

    /** @return BelongsTo<User, $this> */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getDurationMinutesAttribute(): int
    {
        $startedAt = $this->started_at;
        $end = $this->ended_at ?? now();

        return max((int) floor($startedAt->diffInMinutes($end, true)), 0);
    }

    public function getDurationLabelAttribute(): string
    {
        return JobOrderMaintenance::formatDurationMinutes($this->duration_minutes);
    }
}
