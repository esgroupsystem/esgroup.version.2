<?php

declare(strict_types=1);

namespace App\Http\Requests\ITDepartment;

use App\Support\IT\SeatNumbers;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class StoreJobOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tickets.create') ?? false;
    }

    /** The seat map sends "13,12" style lists; store them as "12, 13". */
    protected function prepareForValidation(): void
    {
        if ($this->has('job_sitNumber')) {
            $this->merge(['job_sitNumber' => SeatNumbers::normalize($this->input('job_sitNumber'))]);
        }
    }

    public function rules(): array
    {
        return [
            'bus_detail_id' => [
                'required',
                'integer',
                'exists:bus_details,id',
            ],

            'job_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'job_type' => [
                'required',
                'string',
                'max:255',
            ],

            'job_datestart' => [
                'required',
                'date_format:d/m/y',
            ],

            'job_time_start' => [
                'required',
                'date_format:H:i',
            ],

            'job_time_end' => [
                'required',
                'date_format:H:i',
            ],

            'job_sitNumber' => SeatNumbers::rules(),

            'job_remarks' => [
                'nullable',
                'string',
            ],

            'direction' => [
                'nullable',
                'string',
                'in:South Bound,North Bound',
            ],

            'driver_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'conductor_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'files' => [
                'nullable',
                'array',
                'max:'.config('security.uploads.max_upload_files_per_request', 10),
            ],

            'files.*' => [
                'file',
                'max:'.config('security.uploads.job_order_max_kb', 51200),
                'mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx,txt,jpg,jpeg,png,gif,webp,mp4,webm,ogg,zip',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (\Illuminate\Contracts\Validation\Validator $validator): void {
            $files = $this->file('files', []);
            $totalBytes = 0;

            foreach ($files as $file) {
                $totalBytes += (int) $file->getSize();
            }

            if ($totalBytes > ((int) config('security.uploads.max_upload_total_kb', 102400) * 1024)) {
                $validator->errors()->add('files', 'The combined upload size is too large.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'bus_detail_id.required' => 'Please select a bus.',
            'bus_detail_id.exists' => 'The selected bus no longer exists in the bus master list.',
            'job_datestart.date_format' => 'The incident date must use the DD/MM/YY format.',
            'job_time_start.date_format' => 'The start time must use the HH:MM format.',
            'job_time_end.date_format' => 'The end time must use the HH:MM format.',
            'job_sitNumber.regex' => 'Seat numbers must be numbers separated by commas, e.g. 12, 13.',
        ];
    }
}
