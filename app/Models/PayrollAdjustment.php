<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $payroll_entry_id
 * @property string|null $type
 * @property string|null $label
 * @property string|float|int $amount
 * @property string|null $remarks
 */
class PayrollAdjustment extends Model
{
    protected $fillable = [
        'payroll_entry_id',
        'type',
        'label',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /** @return BelongsTo<PayrollEntry, $this> */
    public function payrollEntry(): BelongsTo
    {
        return $this->belongsTo(PayrollEntry::class);
    }
}
