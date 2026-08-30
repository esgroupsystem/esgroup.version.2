<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $job_id
 * @property string|null $file_name
 * @property string|null $file_remarks
 * @property string|null $file_notes
 * @property string|null $file_path
 */
class JobOrderFile extends Model
{
    protected $fillable = ['job_id', 'file_name', 'file_remarks', 'file_notes', 'file_path'];

    /** @return BelongsTo<JobOrder, $this> */
    public function job(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class, 'job_id');
    }
}
