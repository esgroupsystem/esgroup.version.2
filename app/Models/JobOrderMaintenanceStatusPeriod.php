<?php

namespace App\Models;

use App\Enums\JobOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrderMaintenance::class, 'job_order_maintenance_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getDurationMinutesAttribute(): int
    {
        if (! $this->started_at) {
            return 0;
        }

        $end = $this->ended_at ?? now();

        return max((int) floor($this->started_at->diffInMinutes($end, true)), 0);
    }

    public function getDurationLabelAttribute(): string
    {
        return JobOrderMaintenance::formatDurationMinutes($this->duration_minutes);
    }
}
