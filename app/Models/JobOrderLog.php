<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderLog extends Model
{
    protected $fillable = ['joborder_id','action','meta','user_id'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function joborder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }
}
