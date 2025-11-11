<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderNote extends Model
{
    protected $fillable = ['joborder_id','user_id','reason','details'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function joborder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class, 'joborder_id');
    }
}
