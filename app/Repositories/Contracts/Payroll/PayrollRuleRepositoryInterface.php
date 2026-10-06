<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Payroll;

use App\Models\PayrollRule;
use Illuminate\Support\Collection;

/** Payroll Rules (custom earnings and deductions). */
interface PayrollRuleRepositoryInterface
{
    /** @return Collection<int, PayrollRule> every rule (not deleted), earnings first, then sort order */
    public function all(): Collection;

    /** @return Collection<int, PayrollRule> active rules whose dates overlap the period, in run order */
    public function activeForPeriod(string $periodStart, string $periodEnd): Collection;

    public function find(int $id): ?PayrollRule;

    public function findOrFail(int $id): PayrollRule;

    public function codeTaken(string $code, ?int $ignoreId = null): bool;

    /** @return Collection<int, PayrollRule> rules (not deleted) except one, for formula references */
    public function others(?int $ignoreId = null): Collection;

    /** @param  array<string, mixed>  $attributes */
    public function create(array $attributes): PayrollRule;

    /** @param  array<string, mixed>  $attributes */
    public function update(PayrollRule $rule, array $attributes): PayrollRule;

    public function delete(PayrollRule $rule): void;
}
