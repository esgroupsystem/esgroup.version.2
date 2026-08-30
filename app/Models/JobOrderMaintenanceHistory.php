<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $job_order_maintenance_id
 * @property string|null $user_id
 * @property string|null $action
 * @property string|null $remarks
 * @property string|null $old_value
 * @property string|null $new_value
 */
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

    /** @return BelongsTo<JobOrderMaintenance, $this> */
    public function jobOrderMaintenance(): BelongsTo
    {
        return $this->belongsTo(JobOrderMaintenance::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
