<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderFile extends Model
{
    protected $fillable = ['job_id','file_name','file_remarks','file_notes','file_path'];

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class, 'job_id');
    }
}
