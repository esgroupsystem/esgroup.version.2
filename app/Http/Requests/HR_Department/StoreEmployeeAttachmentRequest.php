<?php

declare(strict_types=1);

namespace App\Http\Requests\HR_Department;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEmployeeAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('employees.update') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'attachment' => [
                'required', 'file',
                'max:'.config('security.uploads.employee_attachment_max_kb', 10240),
                'mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx,txt,jpg,jpeg,png,webp',
            ],
        ];
    }
}
