<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BenefitsRecordIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $today = now('Asia/Manila');

        $this->merge([
            'month' => $this->input('month', (int) $today->month),
            'year' => $this->input('year', (int) $today->year),
            'search' => trim((string) $this->input('search', '')),
            'garage_group' => $this->filled('garage_group')
                ? (int) $this->input('garage_group')
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2020,2100'],
            'search' => ['nullable', 'string', 'max:120'],
            'garage_group' => ['nullable', 'integer', Rule::in([1, 2])],
        ];
    }
}
