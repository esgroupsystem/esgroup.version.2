<?php

namespace App\Http\Requests\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobOrderMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'job_order_no' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9\-\/]+$/',
                Rule::unique('job_orders_maintenance', 'job_order_no'),
            ],
            'bus_id' => ['required', 'integer', 'exists:buses,id'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'description_of_work' => ['required', 'string', 'min:5', 'max:10000'],
            'odometer_reading' => ['nullable', 'integer', 'min:0', 'max:9999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'job_order_no.unique' => 'This job order number is already used.',
            'job_order_no.regex' => 'Job order number may only contain letters, numbers, dash, and slash.',
            'bus_id.required' => 'Please select a bus.',
            'bus_id.exists' => 'The selected bus does not exist.',
            'description_of_work.required' => 'Description of work is required.',
            'description_of_work.min' => 'Description of work must be at least 5 characters.',
            'odometer_reading.integer' => 'Odometer reading must be a valid number.',
            'odometer_reading.min' => 'Odometer reading cannot be negative.',
        ];
    }
}
