<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class GeneratePayrollRequest extends FormRequest
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
        ];
    }
}
