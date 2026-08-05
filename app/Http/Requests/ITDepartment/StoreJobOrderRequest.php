<?php

namespace App\Http\Requests\ITDepartment;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tickets.create') ?? false;
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

            'job_sitNumber' => [
                'nullable',
                'integer',
                'min:1',
                'max:60',
            ],

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
            ],

            'files.*' => [
                'file',
                'max:1024000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'bus_detail_id.required' => 'Please select a bus.',
            'bus_detail_id.exists' => 'The selected bus no longer exists in the bus master list.',
            'job_datestart.date_format' => 'The incident date must use the DD/MM/YY format.',
            'job_time_start.date_format' => 'The start time must use the HH:MM format.',
            'job_time_end.date_format' => 'The end time must use the HH:MM format.',
        ];
    }
}
