<?php

declare(strict_types=1);

namespace App\Http\Requests\HR_Department;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateEmployeeAssetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('employees.update') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'sss_number' => ['nullable', 'string', 'max:50'],
            'tin_number' => ['nullable', 'string', 'max:50'],
            'philhealth_number' => ['nullable', 'string', 'max:50'],
            'pagibig_number' => ['nullable', 'string', 'max:50'],
            'sss_updated_at' => ['nullable', 'date'],
            'tin_updated_at' => ['nullable', 'date'],
            'philhealth_updated_at' => ['nullable', 'date'],
            'pagibig_updated_at' => ['nullable', 'date'],
            'birth_certificate' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'resume' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
            'contract' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx'],
        ];
    }
}
