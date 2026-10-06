<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use App\Models\PayrollRule;
use App\Services\Payroll\PayrollGroupAccessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Create or edit a Payroll Rule (custom earning or deduction). */
final class PayrollRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::fieldRules();
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return self::fieldMessages();
    }

    /**
     * Shared with the test page, which checks an unsaved rule under `compare_rule.`.
     *
     * @return array<string, list<mixed>>
     */
    public static function fieldRules(string $prefix = '', bool $draft = false): array
    {
        return [
            $prefix.'name' => ['required', 'string', 'max:150'],
            $prefix.'code' => [$draft ? 'nullable' : 'required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_]*$/'],
            $prefix.'kind' => ['required', Rule::in(array_keys(PayrollRule::KINDS))],
            $prefix.'method' => ['required', Rule::in(array_keys(PayrollRule::METHODS))],
            $prefix.'amount' => ['nullable', 'required_unless:'.$prefix.'method,formula', 'numeric', 'min:0', 'max:99999999'],
            $prefix.'base' => ['nullable', 'required_if:'.$prefix.'method,percent', Rule::in(array_keys(PayrollRule::moneyVariables()))],
            $prefix.'unit' => ['nullable', 'required_if:'.$prefix.'method,per_unit', Rule::in(array_keys(PayrollRule::unitVariables()))],
            $prefix.'formula' => ['nullable', 'required_if:'.$prefix.'method,formula', 'string', 'max:1000'],
            $prefix.'cutoff' => ['required', Rule::in(array_keys(PayrollRule::CUTOFFS))],
            $prefix.'rate_type' => ['required', Rule::in(array_keys(PayrollRule::RATE_TYPES))],
            $prefix.'payroll_groups' => ['nullable', 'array'],
            $prefix.'payroll_groups.*' => [Rule::in(array_keys(app(PayrollGroupAccessService::class)->options()))],
            $prefix.'employee_biometric_ids' => ['nullable', 'array', 'max:500'],
            $prefix.'employee_biometric_ids.*' => ['integer', 'distinct', Rule::exists('employee_biometrics', 'id')],
            $prefix.'effective_from' => ['nullable', 'date_format:Y-m-d'],
            $prefix.'effective_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.$prefix.'effective_from'],
            $prefix.'is_active' => ['boolean'],
            $prefix.'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            $prefix.'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public static function fieldMessages(string $prefix = ''): array
    {
        return [
            $prefix.'code.regex' => 'Use lower-case letters, numbers and _ only, starting with a letter (e.g. rice_allowance).',
            $prefix.'amount.required_unless' => 'Enter the amount.',
            $prefix.'base.required_if' => 'Pick what the percent is taken from.',
            $prefix.'unit.required_if' => 'Pick what the amount is multiplied by.',
            $prefix.'formula.required_if' => 'Write the formula.',
            $prefix.'payroll_groups.*.in' => 'You can only pick your own payroll groups.',
        ];
    }
}
