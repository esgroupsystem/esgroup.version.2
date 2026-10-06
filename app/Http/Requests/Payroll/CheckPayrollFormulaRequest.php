<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use App\Models\PayrollRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Live formula check while typing a Payroll Rule. */
final class CheckPayrollFormulaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'formula' => ['present', 'nullable', 'string', 'max:1000'],
            'kind' => ['required', Rule::in(array_keys(PayrollRule::KINDS))],
            'rule_id' => ['nullable', 'integer'],
        ];
    }
}
