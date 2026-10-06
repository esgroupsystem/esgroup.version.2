<?php

declare(strict_types=1);

namespace App\Http\Resources\Payroll;

use App\Http\Resources\Concerns\FormatsDates;
use App\Models\EmployeeBiometric;
use App\Models\PayrollRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A Payroll Rule for the rules list.
 *
 * @mixin PayrollRule
 *
 * @property-read PayrollRule $resource
 */
final class PayrollRuleResource extends JsonResource
{
    use FormatsDates;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $rule = $this->resource;
        $groups = array_map(fn ($group): string => EmployeeBiometric::GROUP_LABELS[(int) $group] ?? 'Group '.$group, (array) ($rule->payroll_groups ?? []));
        $people = count((array) ($rule->employee_biometric_ids ?? []));
        $from = $this->formatDate($rule->effective_from, 'M d, Y');
        $to = $this->formatDate($rule->effective_to, 'M d, Y');

        return [
            'id' => $rule->id,
            'name' => $rule->name,
            'code' => $rule->code,
            'kind' => $rule->kind,
            'kind_label' => $rule->isEarning() ? 'Earning' : 'Deduction',
            'method' => $rule->method,
            'method_label' => PayrollRule::METHODS[$rule->method] ?? $rule->method,
            'description' => $rule->describe(),
            'cutoff_label' => PayrollRule::CUTOFFS[$rule->cutoff] ?? $rule->cutoff,
            'applies_to' => implode(' · ', array_filter([
                $rule->rate_type !== 'all' ? PayrollRule::RATE_TYPES[$rule->rate_type] ?? null : null,
                $groups !== [] ? implode(', ', $groups) : null,
                $people > 0 ? $people.' selected employee(s)' : null,
            ])) ?: 'All employees',
            'dates' => match (true) {
                $from !== null && $to !== null => $from.' – '.$to,
                $from !== null => 'From '.$from,
                $to !== null => 'Until '.$to,
                default => 'Always',
            },
            'is_active' => (bool) $rule->is_active,
            'sort_order' => (int) $rule->sort_order,
            'notes' => $rule->notes,
            'updated_by' => $rule->relationLoaded('updater') ? ($rule->updater?->full_name ?: $rule->updater?->username) : null,
            'updated_at' => $this->formatDate($rule->updated_at, 'M d, Y h:i A'),
        ];
    }
}
