<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderMaintenanceHistory extends Model
{
    protected $fillable = [
        'job_order_maintenance_id',
        'user_id',
        'action',
        'remarks',
        'old_value',
        'new_value',
    ];

    public function jobOrderMaintenance(): BelongsTo
    {
        return $this->belongsTo(JobOrderMaintenance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}   
