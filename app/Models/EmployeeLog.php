<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $employee_id
 * @property string|null $action
 * @property array<string, mixed>|null $meta
 * @property string|null $user_id
 */
class EmployeeLog extends Model
{
    protected $fillable = ['employee_id', 'action', 'meta', 'user_id'];

    protected $casts = [
        'meta' => 'array',
    ];

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
