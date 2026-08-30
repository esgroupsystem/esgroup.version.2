<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $employee_id
 * @property string|null $ir_number
 * @property string|null $title
 * @property string|null $offense_id
 * @property array<string, mixed>|null $disciplinary_action
 * @property string|null $sda_amount
 * @property string|null $sda_terms
 * @property \Carbon\CarbonInterface|null $sda_start_date
 * @property \Carbon\CarbonInterface|null $sda_end_date
 * @property string|null $description
 * @property string|null $remarks
 * @property string|null $start_date
 * @property string|null $end_date
 * @property \Carbon\CarbonInterface|null $suspension_start_date
 * @property \Carbon\CarbonInterface|null $suspension_end_date
 */
class EmployeeHistory extends Model
{
    protected $fillable = [
        'employee_id',
        'ir_number',
        'title',
        'offense_id',
        'disciplinary_action',
        'sda_amount',
        'sda_terms',
        'sda_start_date',
        'sda_end_date',
        'description',
        'remarks',
        'start_date',
        'end_date',
        'suspension_start_date',
        'suspension_end_date',
    ];

    protected $casts = [
        'disciplinary_action' => 'array',
        'sda_start_date' => 'date',
        'sda_end_date' => 'date',
        'suspension_start_date' => 'date',
        'suspension_end_date' => 'date',
    ];

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<HrOffense, $this> */
    public function offense(): BelongsTo
    {
        return $this->belongsTo(HrOffense::class, 'offense_id');
    }
}
