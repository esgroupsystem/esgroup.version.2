<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $joborder_id
 * @property string|null $user_id
 * @property string|null $reason
 * @property string|null $details
 */
class JobOrderNote extends Model
{
    protected $fillable = ['joborder_id', 'user_id', 'reason', 'details'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<JobOrder, $this> */
    public function joborder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class, 'joborder_id');
    }
}
