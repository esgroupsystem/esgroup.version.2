<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $employee_id
 * @property string|null $section
 * @property string|null $offense_description
 * @property string|null $offense_type
 * @property string|null $offense_gravity
 */
class HrOffense extends Model
{
    protected $fillable = [
        'employee_id',
        'section',
        'offense_description',
        'offense_type',
        'offense_gravity',
    ];

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
