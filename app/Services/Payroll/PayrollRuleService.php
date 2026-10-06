<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\PayrollRule;
use App\Repositories\Contracts\Payroll\PayrollRuleRepositoryInterface;
use App\Support\Payroll\PayrollFormula;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Payroll Rules: custom earnings and deductions made in Payroll Settings.
 *
 * Rules run in order (earnings first, then sort order). Each result is also available to
 * later rules by its code, so a deduction can use an earning (e.g. `trip_incentive * 10%`).
 * A rule that cannot be computed for one employee gives 0 and records the reason;
 * it never stops the payroll.
 */
final class PayrollRuleService
{
    public function __construct(
        private readonly PayrollRuleRepositoryInterface $rules,
    ) {}

    /** @return Collection<int, PayrollRule> */
    public function list(): Collection
    {
        return $this->rules->all();
    }

    public function findOrFail(int $id): PayrollRule
    {
        return $this->rules->findOrFail($id);
    }

    /** @return Collection<int, PayrollRule> active rules for a payroll period */
    public function forPeriod(string $periodStart, string $periodEnd): Collection
    {
        return $this->rules->activeForPeriod($periodStart, $periodEnd);
    }

    /** @param  array<string, mixed>  $data  validated rule fields */
    public function create(array $data, ?int $userId): PayrollRule
    {
        $attributes = $this->attributes($data);
        $this->assertValid($attributes, null);

        return $this->rules->create($attributes + ['created_by' => $userId, 'updated_by' => $userId]);
    }

    /** @param  array<string, mixed>  $data  validated rule fields */
    public function update(PayrollRule $rule, array $data, ?int $userId): PayrollRule
    {
        $attributes = $this->attributes($data);
        $this->assertValid($attributes, (int) $rule->id);

        return $this->rules->update($rule, $attributes + ['updated_by' => $userId]);
    }

    /**
     * An unsaved rule from form fields, for the test page. It is always active so it can be tried.
     *
     * @param  array<string, mixed>  $data
     */
    public function draft(array $data, ?int $id = null): PayrollRule
    {
        $code = trim((string) ($data['code'] ?? '')) !== '' ? (string) $data['code'] : 'draft_rule';
        $rule = new PayrollRule($this->attributes(['code' => $code] + $data));
        $rule->is_active = true;

        if ($id !== null) {
            $rule->id = $id;
        }

        return $rule;
    }

    public function setActive(PayrollRule $rule, bool $active, ?int $userId): PayrollRule
    {
        return $this->rules->update($rule, ['is_active' => $active, 'updated_by' => $userId]);
    }

    public function delete(PayrollRule $rule): void
    {
        $usedBy = $this->rules->others((int) $rule->id)
            ->filter(fn (PayrollRule $other): bool => $this->usesCode($other, (string) $rule->code))
            ->pluck('name');

        if ($usedBy->isNotEmpty()) {
            throw ValidationException::withMessages([
                'rule' => sprintf('"%s" is used in the formula of: %s. Change those rules first.', $rule->name, $usedBy->implode(', ')),
            ]);
        }

        $this->rules->delete($rule);
    }

    /**
     * Names a formula of this kind may use: the payroll values plus the codes of rules that run before it.
     *
     * @return list<string>
     */
    public function allowedNames(string $kind, ?int $ignoreId = null): array
    {
        $codes = $this->rules->others($ignoreId)
            ->filter(fn (PayrollRule $rule): bool => $kind === PayrollRule::KIND_DEDUCTION || $rule->kind === PayrollRule::KIND_EARNING)
            ->pluck('code')
            ->map(fn ($code): string => strtolower((string) $code))
            ->all();

        return array_values(array_unique([...PayrollRule::variableNames($kind), ...$codes]));
    }

    /**
     * Check a formula without saving; the message is ready for the user.
     *
     * @return array{ok: bool, message: string, names: list<string>}
     */
    public function checkFormula(string $formula, string $kind, ?int $ignoreId = null): array
    {
        try {
            PayrollFormula::parse($formula, $this->allowedNames($kind, $ignoreId));

            return ['ok' => true, 'message' => 'Formula is valid.', 'names' => PayrollFormula::referencedNames($formula)];
        } catch (InvalidArgumentException $exception) {
            return ['ok' => false, 'message' => $exception->getMessage(), 'names' => []];
        }
    }

    /**
     * Run the rules of one kind for one employee.
     *
     * @param  Collection<int, PayrollRule>  $rules  in run order
     * @param  array<string, float|int>  $values  payroll values (PayrollRule::VARIABLES) + codes of earlier results
     * @param  array{cutoff_type: string, rate_type: string, garage_group: string|int|null, employee_biometric_id: int|null}  $context
     * @return array{total: float, lines: list<array<string, mixed>>, values: array<string, float|int>}
     */
    public function run(Collection $rules, string $kind, array $values, array $context): array
    {
        $lines = [];
        $total = 0.0;

        // A rule that does not apply to this employee (or runs later) counts as 0 in formulas.
        foreach ($rules as $rule) {
            $values[strtolower((string) $rule->code)] ??= 0;
        }

        foreach ($rules as $rule) {
            if ($rule->kind !== $kind || ! $this->appliesTo($rule, $context)) {
                continue;
            }

            [$amount, $error] = $this->amount($rule, $values);
            $values[strtolower((string) $rule->code)] = $amount;
            $total += $amount;

            $lines[] = [
                'rule_id' => $rule->id,
                'code' => (string) $rule->code,
                'name' => (string) $rule->name,
                'kind' => $kind,
                'method' => (string) $rule->method,
                'description' => $rule->describe(),
                'amount' => $amount,
                'error' => $error,
            ];
        }

        return ['total' => round($total, 2), 'lines' => $lines, 'values' => $values];
    }

    /** @param  array{cutoff_type: string, rate_type: string, garage_group: string|int|null, employee_biometric_id: int|null}  $context */
    public function appliesTo(PayrollRule $rule, array $context): bool
    {
        if ($rule->cutoff !== 'every' && $rule->cutoff !== $context['cutoff_type']) {
            return false;
        }

        if ($rule->rate_type !== 'all' && $rule->rate_type !== $context['rate_type']) {
            return false;
        }

        $groups = array_map('strval', (array) ($rule->payroll_groups ?? []));
        if ($groups !== [] && ! in_array((string) $context['garage_group'], $groups, true)) {
            return false;
        }

        $people = array_map('intval', (array) ($rule->employee_biometric_ids ?? []));

        return $people === [] || in_array((int) $context['employee_biometric_id'], $people, true);
    }

    /**
     * @param  array<string, float|int>  $values
     * @return array{0: float, 1: string|null} amount (never below 0), error
     */
    private function amount(PayrollRule $rule, array $values): array
    {
        $amount = (float) $rule->amount;

        try {
            $result = match ($rule->method) {
                'fixed' => $amount,
                'percent' => $amount / 100 * (float) ($values[(string) $rule->base] ?? 0),
                'per_unit' => $amount * (float) ($values[(string) $rule->unit] ?? 0),
                'formula' => PayrollFormula::evaluate((string) $rule->formula, $values),
                default => 0.0,
            };
        } catch (InvalidArgumentException $exception) {
            return [0.0, $exception->getMessage()];
        }

        return [round(max(0.0, $result), 2), null];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        $method = (string) $data['method'];

        return [
            'name' => trim((string) $data['name']),
            'code' => strtolower(trim((string) $data['code'])),
            'kind' => (string) $data['kind'],
            'method' => $method,
            'amount' => in_array($method, ['fixed', 'percent', 'per_unit'], true) ? round((float) ($data['amount'] ?? 0), 4) : null,
            'base' => $method === 'percent' ? (string) $data['base'] : null,
            'unit' => $method === 'per_unit' ? (string) $data['unit'] : null,
            'formula' => $method === 'formula' ? trim((string) $data['formula']) : null,
            'cutoff' => (string) ($data['cutoff'] ?? 'every'),
            'rate_type' => (string) ($data['rate_type'] ?? 'all'),
            'payroll_groups' => array_values(array_map('strval', (array) ($data['payroll_groups'] ?? []))) ?: null,
            'employee_biometric_ids' => array_values(array_unique(array_map('intval', (array) ($data['employee_biometric_ids'] ?? [])))) ?: null,
            'effective_from' => $data['effective_from'] ?? null,
            'effective_to' => $data['effective_to'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'notes' => $data['notes'] ?? null,
        ];
    }

    /** @param  array<string, mixed>  $attributes */
    private function assertValid(array $attributes, ?int $ignoreId): void
    {
        if (in_array($attributes['code'], array_keys(PayrollRule::VARIABLES), true)) {
            throw ValidationException::withMessages(['code' => 'This code is already a payroll value name. Pick another code.']);
        }

        if ($this->rules->codeTaken($attributes['code'], $ignoreId)) {
            throw ValidationException::withMessages(['code' => 'Another rule already uses this code.']);
        }

        if ($attributes['method'] !== 'formula') {
            return;
        }

        $check = $this->checkFormula((string) $attributes['formula'], (string) $attributes['kind'], $ignoreId);

        if (! $check['ok']) {
            throw ValidationException::withMessages(['formula' => $check['message']]);
        }

        if (in_array($attributes['code'], $check['names'], true)) {
            throw ValidationException::withMessages(['formula' => 'A rule cannot use its own code in its formula.']);
        }
    }

    private function usesCode(PayrollRule $rule, string $code): bool
    {
        if ($rule->method !== 'formula' || trim((string) $rule->formula) === '') {
            return false;
        }

        try {
            return in_array(strtolower($code), PayrollFormula::referencedNames((string) $rule->formula), true);
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
