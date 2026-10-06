<?php

declare(strict_types=1);

namespace App\Http\Resources\Payroll;

use App\Models\PayrollRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The Payroll Rule form `values`. Wrap null for a new rule (earning, fixed, every cutoff, active).
 *
 * @property-read PayrollRule|null $resource
 */
final class PayrollRuleFormResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $rule = $this->resource;

        return [
            'name' => (string) ($rule->name ?? ''),
            'code' => (string) ($rule->code ?? ''),
            'kind' => (string) ($rule->kind ?? PayrollRule::KIND_EARNING),
            'method' => (string) ($rule->method ?? 'fixed'),
            'amount' => $rule?->amount !== null ? (string) (float) $rule->amount : '',
            'base' => (string) ($rule->base ?? 'basic_pay'),
            'unit' => (string) ($rule->unit ?? 'days_worked'),
            'formula' => (string) ($rule->formula ?? ''),
            'cutoff' => (string) ($rule->cutoff ?? 'every'),
            'rate_type' => (string) ($rule->rate_type ?? 'all'),
            'payroll_groups' => array_map('strval', (array) ($rule->payroll_groups ?? [])),
            'employee_biometric_ids' => array_map('intval', (array) ($rule->employee_biometric_ids ?? [])),
            'effective_from' => $rule?->effective_from?->toDateString() ?? '',
            'effective_to' => $rule?->effective_to?->toDateString() ?? '',
            'is_active' => $rule === null ? true : (bool) $rule->is_active,
            'sort_order' => (int) ($rule->sort_order ?? 0),
            'notes' => (string) ($rule->notes ?? ''),
        ];
    }
}
