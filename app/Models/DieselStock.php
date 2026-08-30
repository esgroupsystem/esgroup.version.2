<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \Carbon\CarbonInterface|null $date
 * @property string|null $type
 * @property string|float|int $liters
 * @property string|float|int $unit_cost
 * @property string|float|int $total_cost
 * @property string|null $bus_detail_id
 * @property string|null $odometer_submission_id
 * @property string|null $reference_no
 * @property string|null $remarks
 * @property string|null $encoded_by
 */
class DieselStock extends Model
{
    protected $fillable = [
        'date',
        'type',
        'liters',
        'unit_cost',
        'total_cost',
        'bus_detail_id',
        'odometer_submission_id',
        'reference_no',
        'remarks',
        'encoded_by',
    ];

    protected $casts = [
        'date' => 'date',
        'liters' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /** @return BelongsTo<BusDetail, $this> */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(BusDetail::class, 'bus_detail_id');
    }

    /** @return BelongsTo<OdometerSubmission, $this> */
    public function odometerSubmission(): BelongsTo
    {
        return $this->belongsTo(OdometerSubmission::class);
    }

    /** @return BelongsTo<User, $this> */
    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }
}
