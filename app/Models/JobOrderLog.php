<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $joborder_id
 * @property string|null $action
 * @property array<string, mixed>|null $meta
 * @property string|null $user_id
 */
class JobOrderLog extends Model
{
    protected $fillable = ['joborder_id', 'action', 'meta', 'user_id'];

    protected $casts = [
        'meta' => 'array',
    ];

    /** @return BelongsTo<JobOrder, $this> */
    public function joborder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
