<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One set of Payroll Settings values, used by every payroll whose period starts
 * on or after `effective_from` (until the next version).
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon $effective_from
 * @property string $label
 * @property array<string, mixed> $values
 * @property string|null $notes
 * @property int|null $created_by
 * @property int|null $updated_by
 */
class PayrollSettingVersion extends Model
{
    protected $fillable = [
        'effective_from',
        'label',
        'values',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'values' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
