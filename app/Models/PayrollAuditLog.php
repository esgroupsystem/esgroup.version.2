<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'user_id',
        'garage_group',
        'module',
        'action',
        'auditable_type',
        'auditable_id',
        'payroll_id',
        'payroll_item_id',
        'employee_biometric_id',
        'employee_id',
        'description',
        'old_values',
        'new_values',
        'context',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'garage_group' => 'integer',
        'auditable_id' => 'integer',
        'payroll_id' => 'integer',
        'payroll_item_id' => 'integer',
        'employee_biometric_id' => 'integer',
        'employee_id' => 'integer',
        'old_values' => 'array',
        'new_values' => 'array',
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }

    public function employeeBiometric(): BelongsTo
    {
        return $this->belongsTo(EmployeeBiometric::class);
    }

    public function scopeForAllowedGroups(Builder $query, string|array|null $allowedGroups): Builder
    {
        if ($allowedGroups === 'all') {
            return $query;
        }

        $groups = collect($allowedGroups ?? [])
            ->map(fn ($group): int => (int) $group)
            ->filter(fn (int $group): bool => in_array($group, [1, 2], true))
            ->unique()
            ->values()
            ->all();

        if ($groups === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $query) use ($groups): void {
            $query->whereIn('garage_group', $groups)
                ->orWhereNull('garage_group');
        });
    }
}
