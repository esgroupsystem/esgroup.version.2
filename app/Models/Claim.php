<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $employee_id
 * @property string|null $claim_type
 * @property string|null $status
 * @property string|null $reference_no
 * @property \Carbon\CarbonInterface|null $date_of_notification
 * @property \Carbon\CarbonInterface|null $date_filed
 * @property \Carbon\CarbonInterface|null $approval_date
 * @property \Carbon\CarbonInterface|null $fund_request_date
 * @property \Carbon\CarbonInterface|null $fund_released_date
 * @property string|null $amount
 * @property string|null $remarks
 * @property string|null $created_by
 * @property string|null $updated_by
 */
class Claim extends Model
{
    protected $fillable = [
        'employee_id',
        'claim_type',
        'status',
        'reference_no',
        'date_of_notification',
        'date_filed',
        'approval_date',
        'fund_request_date',
        'fund_released_date',
        'amount',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_of_notification' => 'date',
        'date_filed' => 'date',
        'approval_date' => 'date',
        'fund_request_date' => 'date',
        'fund_released_date' => 'date',
    ];

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
