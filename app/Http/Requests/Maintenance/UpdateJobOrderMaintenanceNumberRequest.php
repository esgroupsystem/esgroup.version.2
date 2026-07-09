<?php

namespace App\Http\Requests\Maintenance;

use App\Models\JobOrderMaintenance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobOrderMaintenanceNumberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $jobOrderMaintenance = $this->route('jobOrderMaintenance');

        $jobOrderMaintenanceId = $jobOrderMaintenance instanceof JobOrderMaintenance
            ? $jobOrderMaintenance->id
            : $jobOrderMaintenance;

        return [
            'job_order_no' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9\-\/]+$/',
                Rule::unique('job_orders_maintenance', 'job_order_no')
                    ->ignore($jobOrderMaintenanceId),
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'job_order_no.required' => 'Job order number is required.',
            'job_order_no.unique' => 'This job order number is already used.',
            'job_order_no.regex' => 'Job order number may only contain letters, numbers, dash, and slash.',
            'remarks.max' => 'Remarks must not exceed 1000 characters.',
        ];
    }
}
