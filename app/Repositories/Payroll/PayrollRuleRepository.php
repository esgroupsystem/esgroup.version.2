<?php

declare(strict_types=1);

namespace App\Repositories\Payroll;

use App\Models\PayrollRule;
use App\Repositories\Contracts\Payroll\PayrollRuleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class PayrollRuleRepository implements PayrollRuleRepositoryInterface
{
    public function all(): Collection
    {
        return $this->ordered(PayrollRule::query()->with(['creator:id,full_name,username', 'updater:id,full_name,username']))->get();
    }

    public function activeForPeriod(string $periodStart, string $periodEnd): Collection
    {
        return $this->ordered(PayrollRule::query())
            ->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('effective_from')->orWhereDate('effective_from', '<=', $periodEnd))
            ->where(fn (Builder $query) => $query->whereNull('effective_to')->orWhereDate('effective_to', '>=', $periodStart))
            ->get();
    }

    public function find(int $id): ?PayrollRule
    {
        return PayrollRule::query()->find($id);
    }

    public function findOrFail(int $id): PayrollRule
    {
        return PayrollRule::query()->findOrFail($id);
    }

    public function codeTaken(string $code, ?int $ignoreId = null): bool
    {
        return PayrollRule::query()
            ->where('code', $code)
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function others(?int $ignoreId = null): Collection
    {
        return $this->ordered(PayrollRule::query())
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->get();
    }

    public function create(array $attributes): PayrollRule
    {
        return PayrollRule::query()->create($attributes);
    }

    public function update(PayrollRule $rule, array $attributes): PayrollRule
    {
        $rule->update($attributes);

        return $rule;
    }

    public function delete(PayrollRule $rule): void
    {
        $rule->delete();
    }

    /**
     * @param  Builder<PayrollRule>  $query
     * @return Builder<PayrollRule>
     */
    private function ordered(Builder $query): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN kind = ? THEN 0 ELSE 1 END', [PayrollRule::KIND_EARNING])
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
