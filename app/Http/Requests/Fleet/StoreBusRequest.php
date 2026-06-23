<?php

namespace App\Http\Requests\Fleet;

use App\Models\Bus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'operational_status' => $this->operational_status ?: Bus::STATUS_ACTIVE,
            'sale_status' => $this->sale_status ?: Bus::SALE_NOT_FOR_SALE,
        ]);
    }

    public function rules(): array
    {
        return [
            'bus_no' => [
                'required',
                'string',
                'max:50',
                Rule::unique('buses', 'bus_no'),
            ],
            'plate_no' => [
                'nullable',
                'string',
                'max:50',
            ],
            'company' => [
                'nullable',
                'string',
                'max:100',
            ],
            'garage' => [
                'nullable',
                'string',
                'max:100',
            ],
            'chassis_number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'engine_number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'case_number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'operational_status' => [
                'required',
                Rule::in(array_keys(Bus::operationalStatusOptions())),
            ],
            'sale_status' => [
                'required',
                Rule::in(array_keys(Bus::saleStatusOptions())),
            ],
            'monitoring_remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }
}
