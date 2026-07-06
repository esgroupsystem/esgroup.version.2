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
                'string',
                Rule::in(JobOrderStatus::values()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please select a maintenance status.',
            'status.in' => 'The selected maintenance status is invalid.',
        ];
    }
}
