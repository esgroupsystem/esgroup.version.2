<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string|null $name
 * @property \Carbon\CarbonInterface|null $date_from
 * @property \Carbon\CarbonInterface|null $date_to
 * @property string|null $status
 */
class PayrollPeriod extends Model
{
    protected $fillable = [
        'name',
        'date_from',
        'date_to',
        'status',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    /** @return HasMany<PayrollEntry, $this> */
    public function payrollEntries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }
}
