<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use App\Services\Payroll\PayrollGroupAccessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class GeneratePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cutoff_month' => ['required', 'integer', 'min:1', 'max:12'],
            'cutoff_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'cutoff_type' => ['required', 'string', 'in:first,second'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'rebuild_summary' => ['nullable', 'boolean'],
            'garage_group' => [
                'required',
                'integer',
                Rule::in(array_keys(app(PayrollGroupAccessService::class)->options())),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['garage_group.in' => 'You are not allowed to generate payroll for the selected payroll group.'];
    }
}
