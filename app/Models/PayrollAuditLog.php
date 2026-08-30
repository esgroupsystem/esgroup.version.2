<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $request_id
 * @property int|null $user_id
 * @property int|null $garage_group
 * @property string|null $module
 * @property string|null $action
 * @property string|null $auditable_type
 * @property int|null $auditable_id
 * @property int|null $payroll_id
 * @property int|null $payroll_item_id
 * @property int|null $employee_biometric_id
 * @property int|null $employee_id
 * @property string|null $description
 * @property array<string, mixed>|null $old_values
 * @property array<string, mixed>|null $new_values
 * @property array<string, mixed>|null $context
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\CarbonInterface|null $created_at
 */
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

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Payroll, $this> */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    /** @return BelongsTo<PayrollItem, $this> */
    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }

    /** @return BelongsTo<EmployeeBiometric, $this> */
    public function employeeBiometric(): BelongsTo
    {
        return $this->belongsTo(EmployeeBiometric::class);
    }

    /**
     * @param  Builder<PayrollAuditLog>  $query
     * @return Builder<PayrollAuditLog>
     */
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
