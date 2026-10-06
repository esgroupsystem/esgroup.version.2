<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use App\Support\Payroll\PayrollSettingCatalog;
use Illuminate\Foundation\Http\FormRequest;

/** Payroll Settings → contribution calculator (SSS / PhilHealth / Pag-IBIG for one salary). */
final class PayrollContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'salary' => ['required', 'numeric', 'min:0', 'max:10000000'],
            'values' => ['nullable', 'array'],
        ];

        return is_array($this->input('values'))
            ? array_merge($rules, PayrollSettingCatalog::rules('values'))
            : $rules;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return PayrollSettingCatalog::attributes('values');
    }
}
