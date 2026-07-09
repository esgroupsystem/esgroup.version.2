<?php

namespace App\Http\Requests\Maintenance;

use App\Enums\JobOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobOrderMaintenanceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(JobOrderStatus::class),
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
            'status.required' => 'Maintenance status is required.',
            'status.enum' => 'Selected maintenance status is invalid.',
            'remarks.max' => 'Remarks must not exceed 1000 characters.',
        ];
    }
}
