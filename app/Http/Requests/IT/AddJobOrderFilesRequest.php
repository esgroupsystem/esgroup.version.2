<?php

declare(strict_types=1);

namespace App\Http\Requests\ITDepartment;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class AddJobOrderFilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tickets.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'files' => ['nullable', 'array', 'max:'.config('security.uploads.max_upload_files_per_request', 10)],
            'files.*' => [
                'required', 'file',
                'max:'.config('security.uploads.job_order_max_kb', 51200),
                'mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx,txt,jpg,jpeg,png,gif,webp,mp4,webm,ogg,zip',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $totalBytes = 0;
            foreach ($this->file('files', []) as $file) {
                $totalBytes += (int) $file->getSize();
            }
            if ($totalBytes > ((int) config('security.uploads.max_upload_total_kb', 102400) * 1024)) {
                $validator->errors()->add('files', 'The combined upload size is too large.');
            }
        });
    }
}
