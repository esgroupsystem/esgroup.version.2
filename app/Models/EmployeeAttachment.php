<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $employee_id
 * @property string|null $file_name
 * @property string|null $file_path
 * @property string|null $mime_type
 * @property string|null $size
 */
class EmployeeAttachment extends Model
{
    protected $fillable = ['employee_id', 'file_name', 'file_path', 'mime_type', 'size'];

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
